<?php

use App\Jobs\BroadcastQuestion;
use App\Jobs\ProcessIncomingWebhook;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Message;
use App\Models\Newsroom;
use App\Models\User;
use App\Models\WebhookEvent;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;

function postWhatsApp(array $payload, ?string $secret = null): TestResponse
{
    $body = json_encode($payload, JSON_THROW_ON_ERROR);
    $secret ??= (string) config('services.whatsapp.app_secret');
    $signature = 'sha256='.hash_hmac('sha256', $body, $secret);

    return test()->call('POST', '/webhooks/whatsapp', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_X-HUB-SIGNATURE-256' => $signature,
    ], $body);
}

/**
 * @param  array<string, mixed>  $message
 * @return array<string, mixed>
 */
function waPayload(string $from, string $id, array $message, string $phoneId = 'PHONE1'): array
{
    return [
        'object' => 'whatsapp_business_account',
        'entry' => [[
            'id' => 'WABA',
            'changes' => [[
                'field' => 'messages',
                'value' => [
                    'messaging_product' => 'whatsapp',
                    'metadata' => [
                        'display_phone_number' => '15550000000',
                        'phone_number_id' => $phoneId,
                    ],
                    'contacts' => [[
                        'profile' => ['name' => 'Reader'],
                        'wa_id' => $from,
                    ]],
                    'messages' => [[
                        'from' => $from,
                        'id' => $id,
                        'timestamp' => (string) now()->timestamp,
                        ...$message,
                    ]],
                ],
            ]],
        ]],
    ];
}

beforeEach(function () {
    $this->newsroom = Newsroom::factory()->create([
        'wa_phone_number_id' => 'PHONE1',
        'name' => 'كازا حيّ',
    ]);
});

test('webhook verification returns the challenge', function () {
    $this->get('/webhooks/whatsapp?hub.mode=subscribe&hub.verify_token=test-verify-token&hub.challenge=987654')
        ->assertOk()
        ->assertSee('987654', false);
});

test('webhook verification rejects a bad token', function () {
    $this->get('/webhooks/whatsapp?hub.mode=subscribe&hub.verify_token=nope&hub.challenge=987654')
        ->assertForbidden();
});

test('webhook rejects a bad signature', function () {
    postWhatsApp(waPayload('212611111111', 'wamid.1', [
        'type' => 'text',
        'text' => ['body' => 'JOIN'],
    ]), 'wrong-secret')->assertForbidden();

    expect(WebhookEvent::query()->count())->toBe(0);
    expect(Contact::query()->count())->toBe(0);
});

test('webhook queues the payload and returns quickly', function () {
    Queue::fake();

    postWhatsApp(waPayload('212611111111', 'wamid.queued', [
        'type' => 'text',
        'text' => ['body' => 'السلام'],
    ]))->assertOk();

    Queue::assertPushed(ProcessIncomingWebhook::class);
    expect(Message::query()->count())->toBe(0);
    expect(WebhookEvent::query()->count())->toBe(1);
});

test('join stores consent and stop removes it', function () {
    postWhatsApp(waPayload('212622222222', 'wamid.join', [
        'type' => 'text',
        'text' => ['body' => 'JOIN'],
    ]))->assertOk();

    $contact = Contact::query()->first();
    expect($contact)->not->toBeNull()
        ->and($contact->consent_at)->not->toBeNull()
        ->and($contact->consent_source)->toBe('join')
        ->and($contact->opted_out_at)->toBeNull()
        ->and($contact->canReceive())->toBeTrue();

    postWhatsApp(waPayload('212622222222', 'wamid.stop', [
        'type' => 'text',
        'text' => ['body' => 'STOP'],
    ]))->assertOk();

    expect($contact->fresh()->opted_out_at)->not->toBeNull()
        ->and($contact->fresh()->canReceive())->toBeFalse();
});

test('a text reply is stored once and linked to the open campaign', function () {
    $user = User::factory()->create(['newsroom_id' => $this->newsroom->id]);
    $campaign = Campaign::factory()->create([
        'newsroom_id' => $this->newsroom->id,
        'user_id' => $user->id,
        'status' => 'sent',
        'sent_at' => now(),
    ]);

    $payload = waPayload('212633333333', 'wamid.text', [
        'type' => 'text',
        'text' => ['body' => 'الما كيتقطع فسيدي مومن كل صباح'],
    ]);

    postWhatsApp($payload)->assertOk();
    postWhatsApp($payload)->assertOk();

    expect(Message::query()->where('direction', 'in')->count())->toBe(1);

    $message = Message::query()->where('direction', 'in')->first();
    expect($message->campaign_id)->toBe($campaign->id)
        ->and($message->body)->toContain('الما كيتقطع')
        ->and($message->transcript)->not->toBeNull();
});

test('opted-out contacts are skipped by the question broadcast', function () {
    $user = User::factory()->create(['newsroom_id' => $this->newsroom->id]);
    $campaign = Campaign::factory()->create([
        'newsroom_id' => $this->newsroom->id,
        'user_id' => $user->id,
        'status' => 'draft',
    ]);

    Contact::factory()->create([
        'newsroom_id' => $this->newsroom->id,
        'wa_id' => '212644444441',
        'consent_at' => now(),
    ]);
    Contact::factory()->create([
        'newsroom_id' => $this->newsroom->id,
        'wa_id' => '212644444442',
        'consent_at' => now(),
        'opted_out_at' => now(),
    ]);

    BroadcastQuestion::dispatchSync($campaign);

    expect(Message::query()->where('direction', 'out')->count())->toBe(1)
        ->and($campaign->fresh()->status->value)->toBe('sent');
});
