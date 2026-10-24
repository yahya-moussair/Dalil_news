<?php

namespace App\Jobs;

use App\Enums\BroadcastKind;
use App\Enums\CampaignStatus;
use App\Enums\MessageType;
use App\Models\Broadcast;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Newsroom;
use App\Services\Messaging\MessagingManager;
use App\Services\Messaging\OutboundRecorder;
use App\Support\MessageCopy;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class BroadcastQuestion implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public function __construct(public Campaign $campaign) {}

    public function handle(MessagingManager $messaging, OutboundRecorder $recorder): void
    {
        $campaign = $this->campaign->fresh('newsroom');
        $newsroom = $campaign?->newsroom;

        if (! $campaign instanceof Campaign || ! $newsroom instanceof Newsroom) {
            return;
        }

        $neighborhoods = $this->neighborhoods($campaign);
        $contacts = Contact::query()
            ->where('newsroom_id', $campaign->newsroom_id)
            ->whereNotNull('consent_at')
            ->whereNull('opted_out_at')
            ->when($neighborhoods !== [], function ($query) use ($neighborhoods): void {
                $query->whereIn('neighborhood', $neighborhoods);
            })
            ->get();

        $text = MessageCopy::question($newsroom->name, $campaign->question);
        $template = $campaign->template_name ?: 'daily_question';
        $broadcast = Broadcast::query()->create([
            'campaign_id' => $campaign->id,
            'kind' => BroadcastKind::Question,
            'template_name' => $template,
            'total' => $contacts->count(),
            'cost_estimate' => round($contacts->count() * (float) config('dalil.pricing.marketing_usd'), 4),
        ]);

        foreach ($contacts as $contact) {
            $channel = $messaging->forContact($contact);
            $result = $contact->withinServiceWindow()
                ? $channel->sendText($contact->wa_id, $text)
                : $channel->sendTemplate(
                    $contact->wa_id,
                    $template,
                    $campaign->template_lang ?: 'ar',
                    [$newsroom->name, $campaign->question],
                    $text,
                );

            $recorder->store($contact, $campaign, $broadcast, $result, MessageType::Text, $text);
            $broadcast->increment($result->ok ? 'sent' : 'failed');
        }

        $campaign->forceFill([
            'status' => CampaignStatus::Sent,
            'sent_at' => $campaign->sent_at ?? now(),
        ])->save();
    }

    /**
     * @return list<string>
     */
    private function neighborhoods(Campaign $campaign): array
    {
        $audience = $campaign->audience ?? [];
        $raw = $audience['neighborhoods'] ?? [];

        if (! is_array($raw)) {
            return [];
        }

        $names = [];

        foreach ($raw as $name) {
            if (is_string($name) && $name !== '') {
                $names[] = $name;
            }
        }

        return $names;
    }
}
