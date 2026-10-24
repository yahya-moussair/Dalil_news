<?php

namespace App\Http\Controllers;

use App\Enums\MessageDirection;
use App\Http\Controllers\Concerns\ResolvesNewsroom;
use App\Jobs\SendReplyBack;
use App\Models\Campaign;
use App\Models\Contact;
use App\Services\CampaignMetrics;
use App\Support\Presenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReplyBackController extends Controller
{
    use ResolvesNewsroom;

    public function create(Campaign $campaign, CampaignMetrics $metrics): Response
    {
        $campaign = $this->ownedCampaign($campaign);
        $participantIds = $campaign->messages()
            ->where('direction', MessageDirection::In)
            ->distinct()
            ->pluck('contact_id');

        $recipients = Contact::query()
            ->whereIn('id', $participantIds)
            ->whereNotNull('consent_at')
            ->whereNull('opted_out_at')
            ->count();

        $rate = (float) config('dalil.pricing.marketing_usd');

        return Inertia::render('reply-back/compose', [
            'campaign' => Presenter::campaign($campaign, $metrics->summarize($campaign)),
            'recipients' => $recipients,
            'cost_estimate' => round($recipients * $rate, 2),
            'rate' => $rate,
        ]);
    }

    public function store(Request $request, Campaign $campaign): RedirectResponse
    {
        $campaign = $this->ownedCampaign($campaign);
        $data = $request->validate([
            'article_title' => ['required', 'string', 'max:180'],
            'article_url' => ['required', 'url', 'max:500'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $campaign->update([
            'article_title' => $data['article_title'],
            'article_url' => $data['article_url'],
            'article_note' => $data['note'] ?? null,
        ]);

        SendReplyBack::dispatch($campaign);
        Inertia::flash('toast', ['type' => 'success', 'message' => '“You asked, we answered” is queued.']);

        return back();
    }
}
