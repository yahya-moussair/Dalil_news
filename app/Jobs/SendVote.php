<?php

namespace App\Jobs;

use App\Enums\BroadcastKind;
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

class SendVote implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public function __construct(public Campaign $campaign) {}

    public function handle(MessagingManager $messaging, OutboundRecorder $recorder): void
    {
        $campaign = $this->campaign->fresh();

        if (! $campaign instanceof Campaign) {
            return;
        }

        $options = $this->options($campaign);

        if (count($options) < 2) {
            return;
        }

        $replyCount = $campaign->messages()->where('direction', MessageDirection::In)->count();
        $text = MessageCopy::vote($replyCount, $options);
        $rows = [];

        foreach ($options as $option) {
            $rows[] = [
                'id' => 'topic_'.$option['topic_id'],
                'title' => $option['label_ar'],
                'description' => $option['label_fr'],
            ];
        }

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
            'kind' => BroadcastKind::Vote,
            'template_name' => 'vote_prompt',
            'total' => $contacts->count(),
            'cost_estimate' => 0,
        ]);

        foreach ($contacts as $contact) {
            $channel = $messaging->forContact($contact);
            $result = $contact->withinServiceWindow()
                ? $channel->sendChoice($contact->wa_id, $text, $rows)
                : $channel->sendTemplate($contact->wa_id, 'vote_prompt', 'ar', [$text], $text);

            $recorder->store($contact, $campaign, $broadcast, $result, MessageType::Interactive, $text);
            $broadcast->increment($result->ok ? 'sent' : 'failed');
        }
    }

    /**
     * @return list<array{topic_id: int, label_ar: string, label_fr: string}>
     */
    private function options(Campaign $campaign): array
    {
        $options = [];

        foreach ($campaign->vote_options ?? [] as $option) {
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
}
