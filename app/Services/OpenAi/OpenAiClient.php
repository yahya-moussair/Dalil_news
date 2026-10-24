<?php

namespace App\Services\OpenAi;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class OpenAiClient
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function post(string $path, array $payload): Response
    {
        $key = (string) config('services.openai.key');

        if ($key === '') {
            throw new \RuntimeException('OPENAI_API_KEY is not configured.');
        }

        $base = rtrim((string) config('services.openai.base_url', 'https://api.openai.com/v1'), '/');
        $timeout = (int) config('services.openai.timeout', 90);

        return Http::withToken($key)
            ->timeout($timeout)
            ->acceptJson()
            ->post($base.'/'.ltrim($path, '/'), $payload);
    }

    /**
     * @param  array<string, string>  $fields
     */
    public function postMultipart(string $path, array $fields, string $fileContents, string $filename): Response
    {
        $key = (string) config('services.openai.key');

        if ($key === '') {
            throw new \RuntimeException('OPENAI_API_KEY is not configured.');
        }

        $base = rtrim((string) config('services.openai.base_url', 'https://api.openai.com/v1'), '/');
        $timeout = (int) config('services.openai.timeout', 90);

        return Http::withToken($key)
            ->timeout($timeout)
            ->attach('file', $fileContents, $filename)
            ->post($base.'/'.ltrim($path, '/'), $fields);
    }
}
