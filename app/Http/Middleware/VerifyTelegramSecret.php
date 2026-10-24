<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyTelegramSecret
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $secret = (string) config('services.telegram.webhook_secret');
        $skip = (bool) config('services.telegram.skip_secret') && ! app()->isProduction();

        if ($secret === '') {
            if ($skip) {
                return $next($request);
            }

            return response('Telegram webhook secret is not configured.', 403);
        }

        $header = (string) $request->header('X-Telegram-Bot-Api-Secret-Token', '');

        if (! hash_equals($secret, $header)) {
            return response('Invalid Telegram secret.', 403);
        }

        return $next($request);
    }
}
