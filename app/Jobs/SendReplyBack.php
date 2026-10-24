<?php

namespace App\Jobs;

use App\Enums\BroadcastKind;
use App\Enums\CampaignStatus;
use App\Enums\MessageDirection;
use App\Enums\MessageType;
use App\Models\Broadcast;
use App\Models\Campaign;
use App\Models\Contact;
use App\Services\Messaging\MessagingManager;
use App\Services\Messaging\OutboundRecorder;
use App\Support\MessageCopy;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendReplyBack implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public function __construct(public Campaign $campaign) {}

    public function handle(MessagingManager $messaging, OutboundRecorder $recorder): void
    {
        $campaign = $this->campaign->fresh();

        if (! $campaign instanceof Campaign || $campaign->article_title === null || $campaign->article_url === null) {
            return;
        }

        $text = MessageCopy::replyBack($campaign->article_title, $campaign->article_url, $campaign->article_note);
        $contactIds = $campaign->messages()
            ->where('direction', MessageDirection::In)
            ->distinct()
            ->pluck('contact_id');

        $contacts = Contact::query()
            ->whereIn('id', $contactIds)
            ->whereNotNull('consent_at')
            ->whereNull('opted_out_at')
            ->get();

        $broadcast = Broadcast::query()->create([
            'campaign_id' => $campaign->id,
            'kind' => BroadcastKind::ReplyBack,
            'template_name' => 'you_asked_we_answered',
            'total' => $contacts->count(),
            'cost_estimate' => round($contacts->count() * (float) config('dalil.pricing.marketing_usd'), 4),
        ]);

        foreach ($contacts as $contact) {
            $channel = $messaging->forContact($contact);
            $result = $contact->withinServiceWindow()
                ? $channel->sendText($contact->wa_id, $text)
                : $channel->sendTemplate(
                    $contact->wa_id,
                    'you_asked_we_answered',
                    'ar',
                    [$campaign->article_title, $campaign->article_url],
                    $text,
                );

            $recorder->store($contact, $campaign, $broadcast, $result, MessageType::Text, $text);
            $broadcast->increment($result->ok ? 'sent' : 'failed');
        }

        $campaign->forceFill([
            'status' => CampaignStatus::Closed,
            'article_sent_at' => now(),
        ])->save();
    }
}
