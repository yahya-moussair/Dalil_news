<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesNewsroom;
use App\Services\QrCodeSvg;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class OptInController extends Controller
{
    use ResolvesNewsroom;

    public function show(): Response
    {
        $newsroom = $this->newsroom();
        $number = $newsroom->displayNumber();
        $link = $number !== '' ? 'https://wa.me/'.$number.'?text='.rawurlencode('JOIN') : null;

        return Inertia::render('contacts/opt-in', [
            'link' => $link,
            'display_number' => $number !== '' ? $number : null,
            'newsroom' => $newsroom->name,
            'consent_version' => (string) config('dalil.consent_text_version'),
        ]);
    }

    public function qr(): SymfonyResponse
    {
        $number = $this->newsroom()->displayNumber();
        $link = $number !== '' ? 'https://wa.me/'.$number.'?text='.rawurlencode('JOIN') : 'JOIN';

        return response(QrCodeSvg::render($link), 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }
}
