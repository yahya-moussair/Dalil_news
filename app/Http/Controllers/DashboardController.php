<?php

namespace App\Http\Controllers;

use App\Enums\CampaignStatus;
use App\Enums\MessageDirection;
use App\Http\Controllers\Concerns\ResolvesNewsroom;
use App\Models\Contact;
use App\Services\CampaignMetrics;
use App\Support\Presenter;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    use ResolvesNewsroom;

    public function index(CampaignMetrics $metrics): Response
    {
        $newsroom = $this->newsroom();

        $campaigns = $newsroom->campaigns()
            ->withCount([
                'messages as replies_count' => function ($query): void {
                    $query->where('direction', MessageDirection::In);
                },
            ])
            ->latest()
            ->limit(6)
            ->get();

        $cards = [];

        foreach ($campaigns as $campaign) {
            $cards[] = Presenter::campaign($campaign, $metrics->summarize($campaign));
        }

        return Inertia::render('dashboard', [
            'stats' => [
                'subscribers' => Contact::query()
                    ->where('newsroom_id', $newsroom->id)
                    ->whereNotNull('consent_at')
                    ->whereNull('opted_out_at')
                    ->count(),
                'opted_out' => Contact::query()
                    ->where('newsroom_id', $newsroom->id)
                    ->whereNotNull('opted_out_at')
                    ->count(),
                'campaigns' => $newsroom->campaigns()->count(),
                'open' => $newsroom->campaigns()->whereIn('status', [CampaignStatus::Sent, CampaignStatus::Voting])->count(),
            ],
            'campaigns' => $cards,
            'drivers' => [
                'messaging' => (string) config('dalil.drivers.messaging'),
                'transcription' => (string) config('dalil.drivers.transcription'),
                'clustering' => (string) config('dalil.drivers.clustering'),
            ],
        ]);
    }
}
