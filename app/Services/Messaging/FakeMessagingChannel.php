<?php

namespace App\Services\Messaging;

use App\Support\OutboundResult;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FakeMessagingChannel implements MessagingChannel
{
    public function name(): string
    {
        return 'fake';
    }

    public function sendText(string $to, string $body): OutboundResult
    {
        return OutboundResult::ok('fake.'.Str::uuid()->toString(), $body);
    }

    public function sendTemplate(string $to, string $template, string $language, array $parameters, string $fallbackText): OutboundResult
    {
        return OutboundResult::ok('fake.'.Str::uuid()->toString(), $fallbackText);
    }

    public function sendChoice(string $to, string $body, array $rows): OutboundResult
    {
        return $this->sendText($to, $body);
    }

    public function downloadMedia(string $mediaId): string
    {
        $path = self::storagePath($mediaId);

        if (! Storage::disk('local')->exists($path)) {
            Storage::disk('local')->put($path, 'OggS-fake-audio');
        }

        return $path;
    }

    public static function storagePath(string $mediaId): string
    {
        $safe = preg_replace('/[^A-Za-z0-9._-]/', '', $mediaId);

        if (! is_string($safe) || $safe === '') {
            $safe = 'clip';
        }

        return 'voice-notes/'.$safe.'.ogg';
    }
}
