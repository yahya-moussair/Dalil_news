<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyWhatsAppSignature
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $secret = (string) config('services.whatsapp.app_secret');
        $skip = (bool) config('services.whatsapp.skip_signature') && ! app()->isProduction();

        if ($secret === '') {
            if ($skip) {
                return $next($request);
            }

            return response('WhatsApp app secret is not configured.', 403);
        }

        $header = (string) $request->header('X-Hub-Signature-256', '');
        $expected = 'sha256='.hash_hmac('sha256', $request->getContent(), $secret);

        if (! hash_equals($expected, $header)) {
            return response('Invalid signature.', 403);
        }

        return $next($request);
    }
}
