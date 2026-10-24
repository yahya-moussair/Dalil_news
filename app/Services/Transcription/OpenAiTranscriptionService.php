<?php

namespace App\Services\Transcription;

use App\Services\OpenAi\OpenAiClient;

class OpenAiTranscriptionService implements TranscriptionService
{
    public function __construct(private readonly OpenAiClient $client) {}

    public function transcribe(string $absolutePath): array
    {
        $primary = (string) config('services.openai.transcribe_model', 'gpt-transcribe');
        $fallback = (string) config('services.openai.transcribe_fallback_model', 'gpt-4o-mini-transcribe');

        try {
            return $this->request($absolutePath, $primary);
        } catch (\RuntimeException $exception) {
            if ($fallback !== '' && $fallback !== $primary) {
                return $this->request($absolutePath, $fallback);
            }

            throw $exception;
        }
    }

    /**
     * @return array{text: string, language: string|null, confidence: float|null, model: string, provider: string}
     */
    private function request(string $absolutePath, string $model): array
    {
        $contents = file_get_contents($absolutePath);

        if (! is_string($contents) || $contents === '') {
            throw new \RuntimeException('Audio file is empty.');
        }

        $response = $this->client->postMultipart('audio/transcriptions', [
            'model' => $model,
            'prompt' => (string) config('dalil.transcribe_prompt'),
            'response_format' => 'json',
        ], $contents, basename($absolutePath));

        if ($response->failed()) {
            throw new \RuntimeException('Transcription failed for model '.$model.'.');
        }

        $text = $response->json('text');

        if (! is_string($text) || trim($text) === '') {
            throw new \RuntimeException('Transcription returned no text for model '.$model.'.');
        }

        $language = $response->json('language');

        return [
            'text' => trim($text),
            'language' => is_string($language) ? $language : null,
            'confidence' => null,
            'model' => $model,
            'provider' => 'openai',
        ];
    }
}
