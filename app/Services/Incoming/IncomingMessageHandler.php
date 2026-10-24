<?php

namespace App\Services\Incoming;

use App\Enums\CampaignStatus;
use App\Enums\ContactChannel;
use App\Enums\MessageDirection;
use App\Enums\MessageType;
use App\Events\ReplyReceived;
use App\Jobs\NormalizeReply;
use App\Jobs\TranscribeVoiceNote;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Message;
use App\Models\Newsroom;
use App\Models\Topic;
use App\Models\Vote;
use App\Services\Messaging\MessagingManager;
use App\Services\Messaging\OutboundRecorder;
use App\Support\IncomingUpdate;
use App\Support\MessageCopy;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\RateLimiter;

class IncomingMessageHandler
{
    public function __construct(
        private readonly MessagingManager $messaging,
        private readonly OutboundRecorder $recorder,
    ) {}

    public function handle(Newsroom $newsroom, IncomingUpdate $update): void
    {
        if ($update->kind === 'status') {
            $this->applyStatus($update);

            return;
        }

        if ($update->externalId === null || $update->senderId === null) {
            return;
        }

        if (Message::query()->where('wa_message_id', $update->externalId)->exists()) {
            return;
        }

        $channel = $update->channel === 'telegram' ? ContactChannel::Telegram : ContactChannel::WhatsApp;
        $contact = Contact::fromSender($newsroom, $update->senderId, $channel, $update->senderName);

        $limitKey = 'inbound:'.$newsroom->id.':'.$contact->wa_id_hash;

        if (RateLimiter::tooManyAttempts($limitKey, 30)) {
            return;
        }

        RateLimiter::hit($limitKey, 60);

        $contact->forceFill([
            'last_inbound_at' => now(),
            'channel' => $channel,
        ])->save();

        $body = trim((string) $update->body);
        $command = $this->command($body);

        if ($command === 'join') {
            $this->storeInbound($contact, null, $update, MessageType::Text, $body !== '' ? $body : 'JOIN');
            $this->grantConsent($contact);
            $this->sendText($contact, null, MessageCopy::welcome($newsroom->name));

            return;
        }

        if ($command === 'stop') {
            $this->storeInbound($contact, null, $update, MessageType::Text, $body !== '' ? $body : 'STOP');
            $contact->forceFill(['opted_out_at' => now()])->save();
            $this->sendText($contact, null, MessageCopy::stopped());

            return;
        }

        $campaign = $this->latestCampaign($newsroom);

        if ($campaign instanceof Campaign && $campaign->status === CampaignStatus::Voting && $this->looksLikeVote($update, $body)) {
            $this->recordVote($newsroom, $contact, $campaign, $update, $body);

            return;
        }

        $type = match ($update->type) {
            'audio' => MessageType::Audio,
            'interactive' => MessageType::Interactive,
            default => MessageType::Text,
        };

        $message = $this->storeInbound($contact, $campaign, $update, $type, $body !== '' ? $body : null);
        event(new ReplyReceived($message));

        if ($type === MessageType::Audio) {
            TranscribeVoiceNote::dispatch($message);
        } elseif ($type === MessageType::Text && $body !== '') {
            NormalizeReply::dispatch($message);
        }

        if ($campaign instanceof Campaign && $campaign->closes_at !== null && $campaign->closes_at->isPast()) {
            $this->sendText($contact, $campaign, MessageCopy::closed());

            return;
        }

        $this->sendText($contact, $campaign, MessageCopy::ack());
    }

    private function grantConsent(Contact $contact): void
    {
        $version = config('dalil.consent_text_version');

        $contact->forceFill([
            'consent_at' => ($contact->opted_out_at !== null || $contact->consent_at === null) ? now() : $contact->consent_at,
            'opted_out_at' => null,
            'consent_source' => $contact->consent_source ?? 'join',
            'consent_text_version' => is_string($version) ? $version : '2026-10-24',
        ])->save();
    }

    private function recordVote(Newsroom $newsroom, Contact $contact, Campaign $campaign, IncomingUpdate $update, string $body): void
    {
        $this->storeInbound($contact, $campaign, $update, MessageType::Interactive, $body !== '' ? $body : $update->choiceId);

        $topic = $this->resolveVoteTopic($campaign, $update, $body);

        if (! $topic instanceof Topic) {
            $this->sendText($contact, $campaign, MessageCopy::voteInvalid());

            return;
        }

        $existing = Vote::query()
            ->where('campaign_id', $campaign->id)
            ->where('contact_id', $contact->id)
            ->first();

        $changed = $existing instanceof Vote;

        Vote::query()->updateOrCreate(
            ['campaign_id' => $campaign->id, 'contact_id' => $contact->id],
            ['topic_id' => $topic->id],
        );

        $this->sendText($contact, $campaign, MessageCopy::voteSaved($topic->label_ar, $changed));
    }

    private function resolveVoteTopic(Campaign $campaign, IncomingUpdate $update, string $body): ?Topic
    {
        $options = $this->voteOptions($campaign);

        if ($update->choiceId !== null && preg_match('/^topic_(\d+)$/', $update->choiceId, $matches) === 1) {
            $topicId = (int) $matches[1];

            foreach ($options as $option) {
                if ($option['topic_id'] === $topicId) {
                    return Topic::query()->where('campaign_id', $campaign->id)->find($topicId);
                }
            }
        }

        if (preg_match('/^\d{1,2}$/', trim($body)) === 1) {
            $index = (int) trim($body) - 1;
            $option = $options[$index] ?? null;

            if ($option !== null) {
                return Topic::query()->where('campaign_id', $campaign->id)->find($option['topic_id']);
            }
        }

        return null;
    }

    /**
     * @return list<array{topic_id: int, label_ar: string, label_fr: string}>
     */
    private function voteOptions(Campaign $campaign): array
    {
        $raw = $campaign->vote_options ?? [];
        $options = [];

        foreach ($raw as $option) {
            if (! is_array($option)) {
                continue;
            }

            $topicId = $option['topic_id'] ?? null;
            $labelAr = $option['label_ar'] ?? null;
            $labelFr = $option['label_fr'] ?? null;

            if (! is_numeric($topicId) || ! is_string($labelAr) || ! is_string($labelFr)) {
                continue;
            }

            $options[] = [
                'topic_id' => (int) $topicId,
                'label_ar' => $labelAr,
                'label_fr' => $labelFr,
            ];
        }

        return $options;
    }

    private function looksLikeVote(IncomingUpdate $update, string $body): bool
    {
        if ($update->choiceId !== null) {
            return true;
        }

        return preg_match('/^\d{1,2}$/', trim($body)) === 1;
    }

    private function latestCampaign(Newsroom $newsroom): ?Campaign
    {
        $campaign = Campaign::query()
            ->where('newsroom_id', $newsroom->id)
            ->whereIn('status', [CampaignStatus::Sent, CampaignStatus::Voting, CampaignStatus::Closed])
            ->latest('sent_at')
            ->latest('id')
            ->first();

        return $campaign instanceof Campaign ? $campaign : null;
    }

    private function storeInbound(Contact $contact, ?Campaign $campaign, IncomingUpdate $update, MessageType $type, ?string $body): Message
    {
        $receivedAt = $update->timestamp !== null
            ? Carbon::createFromTimestamp($update->timestamp)
            : now();

        return Message::query()->create([
            'campaign_id' => $campaign?->id,
            'contact_id' => $contact->id,
            'wa_message_id' => $update->externalId,
            'direction' => MessageDirection::In,
            'type' => $type,
            'body' => $body,
            'media_id' => $update->mediaId,
            'duration_sec' => $update->duration,
            'status' => 'received',
            'is_test_data' => (bool) $contact->is_test_data,
            'received_at' => $receivedAt,
        ]);
    }

    private function sendText(Contact $contact, ?Campaign $campaign, string $body): void
    {
        try {
            $result = $this->messaging->forContact($contact)->sendText($contact->wa_id, $body);
            $this->recorder->store($contact, $campaign, null, $result, MessageType::Text, $body);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    private function applyStatus(IncomingUpdate $update): void
    {
        if ($update->statusMessageId === null || $update->status === null) {
            return;
        }

        $message = Message::query()->where('wa_message_id', $update->statusMessageId)->first();

        if (! $message instanceof Message || $message->status === $update->status) {
            return;
        }

        $previous = $message->status;
        $message->status = $update->status;
        $message->save();

        $broadcast = $message->broadcast;

        if ($broadcast === null) {
            return;
        }

        if ($update->status === 'delivered' && ! in_array($previous, ['delivered', 'read'], true)) {
            $broadcast->increment('delivered');
        }

        if ($update->status === 'read' && $previous !== 'read') {
            $broadcast->increment('read');

            if (! in_array($previous, ['delivered', 'read'], true)) {
                $broadcast->increment('delivered');
            }
        }

        if ($update->status === 'failed' && $previous !== 'failed') {
            $broadcast->increment('failed');
        }
    }

    private function command(string $body): ?string
    {
        $trimmed = mb_strtolower(trim($body));
        $trimmed = trim($trimmed, " \t\n\r\0\x0B.!،");

        if (in_array($trimmed, ['join', 'start', '/start', 'ابدأ', 'انضم'], true)) {
            return 'join';
        }

        if (in_array($trimmed, ['stop', 'unsubscribe', '/stop', 'arrêt', 'arret', 'وقف', 'إيقاف', 'ايقاف'], true)) {
            return 'stop';
        }

        return null;
    }
}
