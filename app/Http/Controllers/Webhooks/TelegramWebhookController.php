<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessIncomingWebhook;
use App\Models\WebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TelegramWebhookController extends Controller
{
    public function store(Request $request): Response
    {
        /** @var array<string, mixed> $payload */
        $payload = $request->json()->all();

        $event = WebhookEvent::query()->create([
            'provider' => 'telegram',
            'payload' => $payload,
        ]);

        ProcessIncomingWebhook::dispatch($event->id);

        return response('OK', 200);
    }
}
