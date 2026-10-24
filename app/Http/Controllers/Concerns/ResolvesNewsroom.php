<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Campaign;
use App\Models\Newsroom;
use App\Models\User;

trait ResolvesNewsroom
{
    protected function newsroom(): Newsroom
    {
        $user = request()->user();

        if (! $user instanceof User) {
            abort(403);
        }

        $newsroom = $user->newsroom;

        if (! $newsroom instanceof Newsroom) {
            abort(403);
        }

        return $newsroom;
    }

    protected function ownedCampaign(Campaign $campaign): Campaign
    {
        abort_unless($campaign->newsroom_id === $this->newsroom()->id, 404);

        return $campaign;
    }
}
