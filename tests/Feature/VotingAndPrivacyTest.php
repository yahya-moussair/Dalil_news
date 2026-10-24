<?php

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Newsroom;
use App\Models\Topic;
use App\Models\User;
use App\Models\Vote;
use Inertia\Testing\AssertableInertia as Assert;

test('a numbered reply records one vote and can be changed', function () {
    $newsroom = Newsroom::factory()->create(['wa_phone_number_id' => 'PHONE1']);
    $user = User::factory()->create(['newsroom_id' => $newsroom->id]);
    $campaign = Campaign::factory()->create([
        'newsroom_id' => $newsroom->id,
        'user_id' => $user->id,
        'status' => 'voting',
        'sent_at' => now(),
    ]);
    $first = Topic::query()->create([
        'campaign_id' => $campaign->id,
        'label_ar' => 'انقطاع الماء',
        'label_fr' => "Coupures d'eau",
        'replies_count' => 10,
    ]);
    $second = Topic::query()->create([
        'campaign_id' => $campaign->id,
        'label_ar' => 'الإنارة العمومية',
        'label_fr' => 'Éclairage public',
        'replies_count' => 4,
    ]);
    $campaign->update([
        'vote_options' => [
            ['topic_id' => $first->id, 'label_ar' => $first->label_ar, 'label_fr' => $first->label_fr],
            ['topic_id' => $second->id, 'label_ar' => $second->label_ar, 'label_fr' => $second->label_fr],
        ],
    ]);

    $send = function (string $id, string $body) {
        $payload = waPayload('212677777777', $id, [
            'type' => 'text',
            'text' => ['body' => $body],
        ]);
        $raw = json_encode($payload, JSON_THROW_ON_ERROR);
        $signature = 'sha256='.hash_hmac('sha256', $raw, (string) config('services.whatsapp.app_secret'));

        return test()->call('POST', '/webhooks/whatsapp', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-HUB-SIGNATURE-256' => $signature,
        ], $raw);
    };

    $send('wamid.vote1', '2')->assertOk();
    expect(Vote::query()->count())->toBe(1)
        ->and(Vote::query()->first()->topic_id)->toBe($second->id);

    $send('wamid.vote2', '1')->assertOk();
    expect(Vote::query()->count())->toBe(1)
        ->and(Vote::query()->first()->topic_id)->toBe($first->id);
});

test('the dashboard never shows a full phone number or display name', function () {
    $newsroom = Newsroom::factory()->create();
    $user = User::factory()->create(['newsroom_id' => $newsroom->id]);
    $contact = Contact::factory()->create([
        'newsroom_id' => $newsroom->id,
        'wa_id' => '212699900001',
        'display_name' => 'FATIMA-SECRET-NAME',
        'consent_at' => now(),
        'neighborhood' => 'سيدي مومن',
    ]);

    $response = $this->actingAs($user)->get(route('contacts.index'));

    $response->assertOk()
        ->assertDontSee('212699900001', false)
        ->assertDontSee('FATIMA-SECRET-NAME', false)
        ->assertInertia(fn (Assert $page) => $page
            ->component('contacts/index')
            ->where('contacts.data.0.masked_phone', $contact->maskedPhone())
            ->missing('contacts.data.0.display_name'));

    $export = $this->actingAs($user)->get(route('contacts.export'));
    $export->assertOk()->assertDontSee('212699900001', false);
});

test('telegram join is accepted with the secret header', function () {
    Newsroom::factory()->create(['name' => 'كازا حيّ']);

    $payload = json_encode([
        'update_id' => 10,
        'message' => [
            'message_id' => 42,
            'from' => ['id' => 9001, 'first_name' => 'Sara'],
            'chat' => ['id' => 9001, 'type' => 'private'],
            'date' => now()->timestamp,
            'text' => 'JOIN',
        ],
    ], JSON_THROW_ON_ERROR);

    $this->call('POST', '/webhooks/telegram', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X-TELEGRAM-BOT-API-SECRET-TOKEN' => 'test-telegram-secret',
    ], $payload)->assertOk();

    $contact = Contact::query()->first();
    expect($contact)->not->toBeNull()
        ->and($contact->channel->value)->toBe('telegram')
        ->and($contact->consent_at)->not->toBeNull();
});
