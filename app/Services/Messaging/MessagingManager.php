<?php

namespace App\Services\Messaging;

use App\Enums\ContactChannel;
use App\Models\Contact;
use App\Services\TelegramService;
use App\Services\WhatsAppService;

class MessagingManager
{
    public function __construct(
        private readonly FakeMessagingChannel $fake,
        private readonly WhatsAppService $whatsapp,
        private readonly TelegramService $telegram,
    ) {}

    public function driver(?string $name = null): MessagingChannel
    {
        $resolved = $name ?? (string) config('dalil.drivers.messaging', 'fake');

        return match ($resolved) {
            'whatsapp' => $this->whatsapp,
            'telegram' => $this->telegram,
            default => $this->fake,
        };
    }

    public function forContact(Contact $contact): MessagingChannel
    {
        $configured = (string) config('dalil.drivers.messaging', 'fake');

        if ($configured === 'fake') {
            return $this->fake;
        }

        if ($contact->channel === ContactChannel::Telegram) {
            return $this->telegram;
        }

        if ($configured === 'telegram') {
            return $this->telegram;
        }

        return $this->whatsapp;
    }
}
