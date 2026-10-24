<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\Newsroom;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AssignNewsroom
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User && $user->newsroom_id === null) {
            $newsroom = Newsroom::query()->create([
                'name' => 'غرفة الأخبار',
                'city' => 'Casablanca',
                'plan' => 'pilot',
            ]);

            $user->forceFill([
                'newsroom_id' => $newsroom->id,
                'role' => UserRole::Admin,
            ])->save();
        }

        return $next($request);
    }
}
