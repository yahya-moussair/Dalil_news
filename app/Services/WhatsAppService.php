<?php

namespace App\Services;

use App\Services\Messaging\MessagingChannel;
use App\Support\OutboundResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WhatsAppService implements MessagingChannel
{
    public function name(): string
    {
        return 'whatsapp';
    }

    public function sendText(string $to, string $body): OutboundResult
    {
        return $this->postMessage($to, [
            'type' => 'text',
            'text' => ['body' => $body, 'preview_url' => true],
        ], $body);
    }

    public function sendTemplate(string $to, string $template, string $language, array $parameters, string $fallbackText): OutboundResult
    {
        $params = [];

        foreach ($parameters as $parameter) {
            $params[] = ['type' => 'text', 'text' => $parameter];
        }

        return $this->postMessage($to, [
            'type' => 'template',
            'template' => [
                'name' => $template,
                'language' => ['code' => $language],
                'components' => $params === [] ? [] : [[
                    'type' => 'body',
                    'parameters' => $params,
                ]],
            ],
        ], $fallbackText);
    }

    public function sendChoice(string $to, string $body, array $rows): OutboundResult
    {
        $listRows = [];

        foreach (array_slice($rows, 0, 10) as $row) {
            $title = $row['title'];

            if (mb_strlen($title) > 24) {
                $title = mb_substr($title, 0, 23).'…';
            }

            $listRows[] = [
                'id' => $row['id'],
                'title' => $title,
                'description' => mb_substr($row['description'], 0, 72),
            ];
        }

        if ($listRows === []) {
            return $this->sendText($to, $body);
        }

        return $this->postMessage($to, [
            'type' => 'interactive',
            'interactive' => [
                'type' => 'list',
                'body' => ['text' => mb_substr($body, 0, 1024)],
                'action' => [
                    'button' => 'اختاروا',
                    'sections' => [[
                        'title' => 'المواضيع',
                        'rows' => $listRows,
                    ]],
                ],
            ],
        ], $body);
    }

    public function downloadMedia(string $mediaId): string
    {
        $token = $this->token();

        if ($token === '') {
            throw new \RuntimeException('WhatsApp token is not configured.');
        }

        $meta = Http::withToken($token)
            ->timeout(30)
            ->get($this->graphUrl($mediaId));

        if ($meta->failed()) {
            throw new \RuntimeException('WhatsApp media lookup failed.');
        }

        $url = $meta->json('url');

        if (! is_string($url) || $url === '') {
            throw new \RuntimeException('WhatsApp media URL missing.');
        }

        $binary = Http::withToken($token)->timeout(60)->get($url);

        if ($binary->failed()) {
            throw new \RuntimeException('WhatsApp media download failed.');
        }

        $safe = preg_replace('/[^A-Za-z0-9._-]/', '', $mediaId);
        $path = 'voice-notes/'.(is_string($safe) && $safe !== '' ? $safe : Str::uuid()->toString()).'.ogg';
        Storage::disk('local')->put($path, $binary->body());

        return $path;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function postMessage(string $to, array $payload, string $fallbackText): OutboundResult
    {
        $token = $this->token();
        $phoneId = (string) config('services.whatsapp.phone_number_id');

        if ($token === '' || $phoneId === '') {
            return OutboundResult::failed('WhatsApp token or phone number id is not configured.', $fallbackText);
        }

        $response = Http::withToken($token)
            ->timeout(30)
            ->post($this->graphUrl($phoneId.'/messages'), [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $to,
                ...$payload,
            ]);

        if ($response->failed()) {
            return OutboundResult::failed($response->body(), $fallbackText);
        }

        $id = $response->json('messages.0.id');

        return OutboundResult::ok(is_string($id) ? $id : null, $fallbackText);
    }

    private function token(): string
    {
        return (string) config('services.whatsapp.token');
    }

    private function graphUrl(string $path): string
    {
        $version = (string) config('services.whatsapp.graph_version', 'v21.0');

        return 'https://graph.facebook.com/'.$version.'/'.ltrim($path, '/');
    }
}
