<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesNewsroom;
use App\Jobs\ClusterReplies;
use App\Models\Campaign;
use App\Models\Topic;
use App\Services\CampaignMetrics;
use App\Support\Presenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TopicController extends Controller
{
    use ResolvesNewsroom;

    public function index(Campaign $campaign, CampaignMetrics $metrics): Response
    {
        $campaign = $this->ownedCampaign($campaign);
        $topics = $campaign->topics()
            ->with(['messages.contact', 'messages.transcript'])
            ->orderByDesc('replies_count')
            ->get();

        $total = (int) $topics->sum('replies_count');
        $cards = $topics->map(fn (Topic $topic): array => Presenter::topic($topic, $total))->values()->all();

        return Inertia::render('topics/index', [
            'campaign' => Presenter::campaign($campaign, $metrics->summarize($campaign)),
            'topics' => $cards,
            'total' => $total,
        ]);
    }

    public function update(Request $request, Campaign $campaign, Topic $topic): RedirectResponse
    {
        $campaign = $this->ownedCampaign($campaign);
        abort_unless($topic->campaign_id === $campaign->id, 404);

        $data = $request->validate([
            'label_ar' => ['required', 'string', 'max:120'],
            'label_fr' => ['required', 'string', 'max:120'],
            'summary' => ['nullable', 'string', 'max:500'],
        ]);

        $topic->update([
            'label_ar' => $data['label_ar'],
            'label_fr' => $data['label_fr'],
            'summary' => $data['summary'] ?? $topic->summary,
            'is_locked' => true,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Topic renamed and locked.']);

        return back();
    }

    public function merge(Request $request, Campaign $campaign): RedirectResponse
    {
        $campaign = $this->ownedCampaign($campaign);
        $data = $request->validate([
            'source_id' => ['required', 'integer'],
            'target_id' => ['required', 'integer', 'different:source_id'],
        ]);

        $source = $campaign->topics()->with('messages')->findOrFail($data['source_id']);
        $target = $campaign->topics()->findOrFail($data['target_id']);

        if (! $source instanceof Topic || ! $target instanceof Topic) {
            abort(404);
        }

        foreach ($source->messages as $message) {
            $already = $target->messages()->where('messages.id', $message->id)->exists();

            if (! $already) {
                $target->messages()->attach($message->id, [
                    'score' => 0.5,
                    'is_featured_quote' => false,
                ]);
            }
        }

        $target->forceFill([
            'replies_count' => $target->messages()->count(),
            'is_locked' => true,
        ])->save();
        $source->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Topics merged.']);

        return back();
    }

    public function recluster(Campaign $campaign): RedirectResponse
    {
        $campaign = $this->ownedCampaign($campaign);
        ClusterReplies::dispatch($campaign, true);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Topics are being rebuilt.']);

        return back();
    }
}
