<?php

namespace App\Http\Controllers;

use App\Enums\MessageDirection;
use App\Http\Controllers\Concerns\ResolvesNewsroom;
use App\Models\Contact;
use App\Services\CampaignMetrics;
use App\Support\Presenter;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class MetricsController extends Controller
{
    use ResolvesNewsroom;

    public function index(CampaignMetrics $metrics): Response
    {
        $newsroom = $this->newsroom();
        $campaigns = $newsroom->campaigns()->latest()->get();
        $rows = [];

        foreach ($campaigns as $campaign) {
            $rows[] = Presenter::campaign($campaign, $metrics->summarize($campaign));
        }

        $neighborhoods = [];

        $neighborhoodRows = DB::table('contacts')
            ->select('neighborhood', DB::raw('count(*) as total'))
            ->where('newsroom_id', $newsroom->id)
            ->whereNotNull('neighborhood')
            ->whereNotNull('consent_at')
            ->whereNull('opted_out_at')
            ->groupBy('neighborhood')
            ->orderByDesc('total')
            ->get();

        foreach ($neighborhoodRows as $row) {
            $data = (array) $row;
            $name = $data['neighborhood'] ?? '';

            if (! is_string($name) || $name === '') {
                continue;
            }

            $neighborhoods[] = [
                'name' => $name,
                'total' => (int) ($data['total'] ?? 0),
            ];
        }

        $voice = $newsroom->campaigns()
            ->join('messages', 'messages.campaign_id', '=', 'campaigns.id')
            ->where('messages.direction', MessageDirection::In->value)
            ->where('messages.type', 'audio')
            ->count();

        $replies = $newsroom->campaigns()
            ->join('messages', 'messages.campaign_id', '=', 'campaigns.id')
            ->where('messages.direction', MessageDirection::In->value)
            ->count();

        return Inertia::render('metrics/index', [
            'campaigns' => $rows,
            'neighborhoods' => $neighborhoods,
            'overview' => [
                'subscribers' => Contact::query()->where('newsroom_id', $newsroom->id)->whereNotNull('consent_at')->whereNull('opted_out_at')->count(),
                'opted_out' => Contact::query()->where('newsroom_id', $newsroom->id)->whereNotNull('opted_out_at')->count(),
                'replies' => $replies,
                'voice_percent' => $replies > 0 ? (int) round(($voice / $replies) * 100) : 0,
                'neighborhoods' => count($neighborhoods),
            ],
        ]);
    }
}
