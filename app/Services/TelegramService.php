<?php

namespace App\Services;

use App\Services\Messaging\MessagingChannel;
use App\Support\OutboundResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TelegramService implements MessagingChannel
{
    public function name(): string
    {
        return 'telegram';
    }

    public function sendText(string $to, string $body): OutboundResult
    {
        $token = $this->token();

        if ($token === '') {
            return OutboundResult::failed('Telegram bot token is not configured.', $body);
        }

        $response = Http::timeout(30)->post($this->method('sendMessage'), [
            'chat_id' => $to,
            'text' => $body,
        ]);

        if ($response->failed() || $response->json('ok') !== true) {
            return OutboundResult::failed($response->body(), $body);
        }

        $id = $response->json('result.message_id');

        return OutboundResult::ok($id === null ? null : 'tg.'.$id, $body);
    }

    public function sendTemplate(string $to, string $template, string $language, array $parameters, string $fallbackText): OutboundResult
    {
        return $this->sendText($to, $fallbackText);
    }

    public function sendChoice(string $to, string $body, array $rows): OutboundResult
    {
        return $this->sendText($to, $body);
    }

    public function downloadMedia(string $mediaId): string
    {
        $token = $this->token();

        if ($token === '') {
            throw new \RuntimeException('Telegram bot token is not configured.');
        }

        $meta = Http::timeout(30)->post($this->method('getFile'), [
            'file_id' => $mediaId,
        ]);

        $filePath = $meta->json('result.file_path');

        if (! is_string($filePath) || $filePath === '') {
            throw new \RuntimeException('Telegram file path missing.');
        }

        $binary = Http::timeout(60)->get('https://api.telegram.org/file/bot'.$token.'/'.$filePath);

        if ($binary->failed()) {
            throw new \RuntimeException('Telegram file download failed.');
        }

        $safe = preg_replace('/[^A-Za-z0-9._-]/', '', $mediaId);
        $path = 'voice-notes/tg-'.(is_string($safe) && $safe !== '' ? $safe : Str::uuid()->toString()).'.ogg';
        Storage::disk('local')->put($path, $binary->body());

        return $path;
    }

    private function token(): string
    {
        return (string) config('services.telegram.bot_token');
    }

    private function method(string $method): string
    {
        return 'https://api.telegram.org/bot'.$this->token().'/'.$method;
    }
}
