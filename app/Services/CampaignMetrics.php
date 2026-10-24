<?php

namespace App\Services;

use App\Enums\BroadcastKind;
use App\Enums\MessageDirection;
use App\Enums\MessageType;
use App\Models\Broadcast;
use App\Models\Campaign;
use App\Models\Contact;

class CampaignMetrics
{
    /**
     * @return array{
     *     replies: int,
     *     participants: int,
     *     voice: int,
     *     voice_percent: int,
     *     subscribers_reached: int|null,
     *     response_rate: int|null,
     *     neighborhoods: int,
     *     read_rate: int|null,
     *     days_to_article: int|null,
     *     has_test_data: bool
     * }
     */
    public function summarize(Campaign $campaign): array
    {
        $inbound = $campaign->messages()->where('direction', MessageDirection::In);
        $replies = (clone $inbound)->count();
        $voice = (clone $inbound)->where('type', MessageType::Audio)->count();
        $participants = (int) (clone $inbound)->distinct()->count('contact_id');

        $neighborhoods = Contact::query()
            ->whereIn('id', (clone $inbound)->select('contact_id'))
            ->whereNotNull('neighborhood')
            ->distinct()
            ->count('neighborhood');

        $question = $campaign->broadcasts()->where('kind', BroadcastKind::Question)->latest('id')->first();
        $reached = $question instanceof Broadcast ? $question->total : null;
        $responseRate = $reached !== null && $reached > 0
            ? (int) round(($participants / $reached) * 100)
            : null;

        $replyBack = $campaign->broadcasts()->where('kind', BroadcastKind::ReplyBack)->latest('id')->first();
        $readRate = $replyBack instanceof Broadcast && $replyBack->sent > 0
            ? (int) round(($replyBack->read / $replyBack->sent) * 100)
            : null;

        $days = null;

        if ($campaign->sent_at !== null && $campaign->article_sent_at !== null) {
            $days = (int) $campaign->sent_at->diffInDays($campaign->article_sent_at);
        }

        return [
            'replies' => $replies,
            'participants' => $participants,
            'voice' => $voice,
            'voice_percent' => $replies > 0 ? (int) round(($voice / $replies) * 100) : 0,
            'subscribers_reached' => $reached,
            'response_rate' => $responseRate,
            'neighborhoods' => $neighborhoods,
            'read_rate' => $readRate,
            'days_to_article' => $days,
            'has_test_data' => $campaign->messages()->where('is_test_data', true)->exists(),
        ];
    }
}
