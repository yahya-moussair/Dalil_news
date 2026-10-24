<?php

namespace App\Http\Middleware;

use App\Models\Newsroom;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $locale = $request->cookie('locale', 'ar');

        if (! is_string($locale) || ! in_array($locale, ['ar', 'fr', 'en'], true)) {
            $locale = 'ar';
        }

        $user = $request->user();
        $newsroom = null;

        if ($user instanceof User) {
            $user->unsetRelation('newsroom');
        }

        if ($user instanceof User && $user->newsroom_id !== null) {
            $room = Newsroom::query()->find($user->newsroom_id);

            if ($room instanceof Newsroom) {
                $newsroom = [
                    'id' => $room->id,
                    'name' => $room->name,
                    'city' => $room->city,
                ];
            }
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
            ],
            'newsroom' => $newsroom,
            'locale' => $locale,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'demoHint' => app()->environment('local'),
            'drivers' => [
                'messaging' => (string) config('dalil.drivers.messaging'),
                'transcription' => (string) config('dalil.drivers.transcription'),
                'clustering' => (string) config('dalil.drivers.clustering'),
            ],
        ];
    }
}
