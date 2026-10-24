<?php

use App\Services\Clustering\FakeClusteringService;
use App\Services\Clustering\OpenAiClusteringService;
use App\Services\OpenAi\OpenAiClient;
use App\Services\Transcription\OpenAiTranscriptionService;
use App\Support\Phone;
use App\Support\QuoteAnonymizer;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

test('phone numbers are masked and quotes drop them', function () {
    expect(Phone::mask(Phone::normalize('+212 6 12 34 56 78')))->not->toContain('123456')
        ->and(QuoteAnonymizer::clean('كلمني على 212611122233 بسرعة'))->toContain('[رقم محذوف]')
        ->and(QuoteAnonymizer::clean('كلمني على 212611122233 بسرعة'))->not->toContain('212611122233');
});

test('fake clustering separates water from lighting', function () {
    $service = new FakeClusteringService;
    $topics = $service->cluster([
        ['message_id' => 1, 'text' => 'الما كيتقطع فسيدي مومن', 'language' => 'ary', 'neighborhood' => 'سيدي مومن'],
        ['message_id' => 2, 'text' => "l'eau est coupée à Bernoussi", 'language' => 'fr', 'neighborhood' => 'البرنوصي'],
        ['message_id' => 3, 'text' => 'الإنارة مطفية فمولاي رشيد', 'language' => 'ary', 'neighborhood' => 'مولاي رشيد'],
    ]);

    $labels = array_column($topics, 'label_ar');
    expect($labels)->toContain('انقطاع الماء')->toContain('الإنارة العمومية');
});

test('openai transcription falls back when the primary model fails', function () {
    config([
        'services.openai.key' => 'test-key',
        'services.openai.base_url' => 'https://api.openai.com/v1',
        'services.openai.transcribe_model' => 'gpt-transcribe',
        'services.openai.transcribe_fallback_model' => 'gpt-4o-mini-transcribe',
        'services.openai.timeout' => 5,
    ]);

    Http::fake([
        'api.openai.com/v1/audio/transcriptions' => Http::sequence()
            ->push(['error' => 'missing model'], 404)
            ->push(['text' => 'الما كيتقطع'], 200),
    ]);

    $path = tempnam(sys_get_temp_dir(), 'ogg');
    file_put_contents((string) $path, 'OggS');

    $result = (new OpenAiTranscriptionService(new OpenAiClient))->transcribe((string) $path);

    expect($result['text'])->toBe('الما كيتقطع')
        ->and($result['model'])->toBe('gpt-4o-mini-transcribe')
        ->and($result['provider'])->toBe('openai');
});

test('openai clustering parses topic ids from the chat model', function () {
    config([
        'services.openai.key' => 'test-key',
        'services.openai.base_url' => 'https://api.openai.com/v1',
        'services.openai.chat_model' => 'gpt-4o-mini',
        'services.openai.timeout' => 5,
    ]);

    Http::fake([
        'api.openai.com/v1/chat/completions' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => json_encode([
                        'topics' => [[
                            'label_ar' => 'النفايات',
                            'label_fr' => 'Déchets',
                            'summary' => 'الأزبال متراكمة.',
                            'message_ids' => [7],
                            'featured_message_ids' => [7],
                        ]],
                    ], JSON_UNESCAPED_UNICODE),
                ],
            ]],
        ]),
    ]);

    $topics = (new OpenAiClusteringService(new OpenAiClient, new FakeClusteringService))->cluster([
        ['message_id' => 7, 'text' => 'الزبل قرب السوق', 'language' => 'ary', 'neighborhood' => 'الحي المحمدي'],
    ]);

    expect($topics[0]['label_ar'])->toBe('النفايات')
        ->and($topics[0]['message_ids'])->toBe([7]);
});
