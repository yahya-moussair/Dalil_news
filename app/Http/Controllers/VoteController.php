<?php

namespace App\Http\Controllers;

use App\Enums\CampaignStatus;
use App\Http\Controllers\Concerns\ResolvesNewsroom;
use App\Jobs\SendVote;
use App\Models\Campaign;
use App\Models\Topic;
use App\Services\CampaignMetrics;
use App\Support\Presenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VoteController extends Controller
{
    use ResolvesNewsroom;

    public function show(Campaign $campaign, CampaignMetrics $metrics): Response
    {
        $campaign = $this->ownedCampaign($campaign);

        $topics = $campaign->topics()
            ->orderByDesc('replies_count')
            ->get(['id', 'label_ar', 'label_fr', 'replies_count'])
            ->map(fn (Topic $topic): array => [
                'id' => $topic->id,
                'label_ar' => $topic->label_ar,
                'label_fr' => $topic->label_fr,
                'replies_count' => $topic->replies_count,
            ])
            ->values()
            ->all();

        return Inertia::render('votes/show', [
            'campaign' => Presenter::campaign($campaign, $metrics->summarize($campaign)),
            'topics' => $topics,
            'tally' => Presenter::voteTally($campaign),
        ]);
    }

    public function store(Request $request, Campaign $campaign): RedirectResponse
    {
        $campaign = $this->ownedCampaign($campaign);
        $data = $request->validate([
            'topic_ids' => ['required', 'array', 'min:2', 'max:4'],
            'topic_ids.*' => ['integer'],
        ]);

        $topics = $campaign->topics()->whereIn('id', $data['topic_ids'])->get()->keyBy('id');
        $options = [];

        foreach ($data['topic_ids'] as $id) {
            $topic = $topics->get($id);

            if (! $topic instanceof Topic) {
                continue;
            }

            $options[] = [
                'topic_id' => $topic->id,
                'label_ar' => $topic->label_ar,
                'label_fr' => $topic->label_fr,
            ];
        }

        if (count($options) < 2) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Pick 2 to 4 topics from this campaign.']);

            return back();
        }

        $campaign->update([
            'status' => CampaignStatus::Voting,
            'vote_options' => $options,
        ]);

        SendVote::dispatch($campaign);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Vote sent to participants.']);

        return back();
    }
}
