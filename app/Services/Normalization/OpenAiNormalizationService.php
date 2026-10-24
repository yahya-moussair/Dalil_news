<?php

namespace App\Services\Normalization;

use App\Services\OpenAi\OpenAiClient;

class OpenAiNormalizationService implements NormalizationService
{
    public function __construct(
        private readonly OpenAiClient $client,
        private readonly FakeNormalizationService $fallback,
    ) {}

    public function normalize(string $text): array
    {
        $model = (string) config('services.openai.chat_model', 'gpt-4o-mini');

        $response = $this->client->post('chat/completions', [
            'model' => $model,
            'temperature' => 0.2,
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You normalize audience replies for a Moroccan newsroom. Return JSON {"language":"ary|ar|fr|arabizi","normalized_text":"...","summary_msa":"..."}. language is ary for Darija in Arabic script, arabizi for Latin Darija, ar for MSA, fr for French. normalized_text keeps the meaning; convert Arabizi into Arabic script. summary_msa is one short Modern Standard Arabic sentence. Do not invent facts and do not include phone numbers.',
                ],
                ['role' => 'user', 'content' => $text],
            ],
        ]);

        if ($response->failed()) {
            return $this->fallback->normalize($text);
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content)) {
            return $this->fallback->normalize($text);
        }

        $decoded = json_decode($content, true);

        if (! is_array($decoded)) {
            return $this->fallback->normalize($text);
        }

        $language = $decoded['language'] ?? 'ar';
        $normalized = $decoded['normalized_text'] ?? $text;
        $summary = $decoded['summary_msa'] ?? $normalized;

        if (! is_string($language) || ! in_array($language, ['ary', 'ar', 'fr', 'arabizi'], true)) {
            $language = 'ar';
        }

        if (! is_string($normalized) || trim($normalized) === '') {
            $normalized = $text;
        }

        if (! is_string($summary) || trim($summary) === '') {
            $summary = $normalized;
        }

        return [
            'language' => $language,
            'normalized_text' => trim($normalized),
            'summary_msa' => trim($summary),
        ];
    }
}
