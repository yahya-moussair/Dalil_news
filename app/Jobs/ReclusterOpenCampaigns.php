<?php

namespace App\Jobs;

use App\Enums\CampaignStatus;
use App\Models\Campaign;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ReclusterOpenCampaigns implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        Campaign::query()
            ->whereIn('status', [CampaignStatus::Sent, CampaignStatus::Voting])
            ->each(function (Campaign $campaign): void {
                $latest = $campaign->messages()->max('updated_at');

                if ($campaign->clustered_at !== null && is_string($latest) && $campaign->clustered_at->greaterThanOrEqualTo($latest)) {
                    return;
                }

                ClusterReplies::dispatch($campaign);
            });
    }
}
