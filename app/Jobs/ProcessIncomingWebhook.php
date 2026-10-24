<?php

namespace App\Jobs;

use App\Models\Newsroom;
use App\Models\WebhookEvent;
use App\Services\Incoming\IncomingMessageHandler;
use App\Services\Incoming\TelegramPayloadParser;
use App\Services\Incoming\WhatsAppPayloadParser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessIncomingWebhook implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $webhookEventId) {}

    public function handle(
        IncomingMessageHandler $handler,
        WhatsAppPayloadParser $whatsApp,
        TelegramPayloadParser $telegram,
    ): void {
        $event = WebhookEvent::query()->find($this->webhookEventId);

        if (! $event instanceof WebhookEvent || $event->processed_at !== null) {
            return;
        }

        try {
            $updates = $event->provider === 'telegram'
                ? $telegram->parse($event->payload)
                : $whatsApp->parse($event->payload);

            foreach ($updates as $update) {
                $newsroom = Newsroom::resolveForPhoneNumberId($update->phoneNumberId);

                if (! $newsroom instanceof Newsroom) {
                    throw new \RuntimeException('No newsroom is configured for this webhook.');
                }

                $handler->handle($newsroom, $update);
            }

            $event->forceFill([
                'processed_at' => now(),
                'error' => null,
            ])->save();
        } catch (\Throwable $exception) {
            $event->forceFill(['error' => mb_substr($exception->getMessage(), 0, 500)])->save();

            throw $exception;
        }
    }
}
