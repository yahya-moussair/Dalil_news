<?php

use App\Jobs\ClusterReplies;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Message;
use App\Models\Newsroom;
use App\Models\Topic;
use App\Models\User;
use App\Services\Clustering\ClusteringService;
use App\Services\Transcription\TranscriptionService;
use Inertia\Testing\AssertableInertia as Assert;

test('a voice note is transcribed through the transcription service', function () {
    $newsroom = Newsroom::factory()->create(['wa_phone_number_id' => 'PHONE1']);
    $user = User::factory()->create(['newsroom_id' => $newsroom->id]);
    Campaign::factory()->create([
        'newsroom_id' => $newsroom->id,
        'user_id' => $user->id,
        'status' => 'sent',
        'sent_at' => now(),
    ]);

    $this->mock(TranscriptionService::class, function ($mock): void {
        $mock->shouldReceive('transcribe')->once()->andReturn([
            'text' => 'الما كيتقطع فسيدي مومن من الصباح',
            'language' => null,
            'confidence' => 0.91,
            'model' => 'gpt-transcribe',
            'provider' => 'openai',
        ]);
    });

    $body = json_encode(waPayload('212655555555', 'wamid.audio', [
        'type' => 'audio',
        'audio' => ['id' => 'media-99', 'mime_type' => 'audio/ogg'],
    ]), JSON_THROW_ON_ERROR);
    $signature = 'sha256='.hash_hmac('sha256', $body, (string) config('services.whatsapp.app_secret'));

    $this->call('POST', '/webhooks/whatsapp', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X-HUB-SIGNATURE-256' => $signature,
    ], $body)->assertOk();

    $message = Message::query()->where('type', 'audio')->first();
    expect($message)->not->toBeNull()
        ->and($message->transcript->raw_text)->toBe('الما كيتقطع فسيدي مومن من الصباح')
        ->and($message->transcript->normalized_text)->not->toBeEmpty()
        ->and($message->media_path)->not->toBeNull();
});

test('clustering uses the clustering service and keeps real message ids', function () {
    $newsroom = Newsroom::factory()->create();
    $user = User::factory()->create(['newsroom_id' => $newsroom->id]);
    $campaign = Campaign::factory()->create([
        'newsroom_id' => $newsroom->id,
        'user_id' => $user->id,
        'status' => 'sent',
    ]);
    $contact = Contact::factory()->create(['newsroom_id' => $newsroom->id]);
    $message = Message::factory()->create([
        'campaign_id' => $campaign->id,
        'contact_id' => $contact->id,
        'body' => 'اتصلوا بي على 212611122233 من فضلكم',
    ]);
    $message->transcript()->create([
        'raw_text' => 'اتصلوا بي على 212611122233 من فضلكم',
        'normalized_text' => 'اتصلوا بي على 212611122233 من فضلكم',
        'language' => 'ar',
    ]);

    $this->mock(ClusteringService::class, function ($mock) use ($message): void {
        $mock->shouldReceive('cluster')->once()->andReturn([[
            'label_ar' => 'انقطاع الماء',
            'label_fr' => "Coupures d'eau",
            'summary' => 'الماء منقطع.',
            'message_ids' => [$message->id],
            'featured_message_ids' => [$message->id],
        ]]);
    });

    ClusterReplies::dispatchSync($campaign, true);

    $topic = Topic::query()->first();
    expect($topic)->not->toBeNull()
        ->and($topic->label_ar)->toBe('انقطاع الماء')
        ->and($topic->label_fr)->toBe("Coupures d'eau")
        ->and($topic->replies_count)->toBe(1)
        ->and($topic->messages()->where('messages.id', $message->id)->exists())->toBeTrue();

    $viewer = User::factory()->create(['newsroom_id' => $newsroom->id]);
    $this->actingAs($viewer)
        ->get(route('campaigns.topics', $campaign))
        ->assertOk()
        ->assertDontSee('212611122233', false)
        ->assertInertia(fn (Assert $page) => $page
            ->component('topics/index')
            ->where('topics.0.quotes.0.text', 'اتصلوا بي على [رقم محذوف] من فضلكم'));
});

test('reclustering replaces unlocked topics', function () {
    $newsroom = Newsroom::factory()->create();
    $user = User::factory()->create(['newsroom_id' => $newsroom->id]);
    $campaign = Campaign::factory()->create([
        'newsroom_id' => $newsroom->id,
        'user_id' => $user->id,
        'status' => 'sent',
    ]);
    $contact = Contact::factory()->create(['newsroom_id' => $newsroom->id]);
    $message = Message::factory()->create([
        'campaign_id' => $campaign->id,
        'contact_id' => $contact->id,
        'body' => 'الما كيتقطع',
    ]);
    $message->transcript()->create([
        'raw_text' => 'الما كيتقطع',
        'normalized_text' => 'الما كيتقطع',
        'language' => 'ary',
    ]);

    Topic::query()->create([
        'campaign_id' => $campaign->id,
        'label_ar' => 'قديم',
        'label_fr' => 'Ancien',
        'replies_count' => 1,
        'is_locked' => false,
    ]);

    ClusterReplies::dispatchSync($campaign, true);

    expect(Topic::query()->where('label_ar', 'قديم')->exists())->toBeFalse()
        ->and(Topic::query()->where('label_ar', 'انقطاع الماء')->exists())->toBeTrue();
});
