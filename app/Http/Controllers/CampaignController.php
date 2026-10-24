<?php

namespace App\Http\Controllers;

use App\Enums\ContactChannel;
use App\Enums\MessageType;
use App\Http\Controllers\Concerns\ResolvesNewsroom;
use App\Http\Requests\StoreCampaignRequest;
use App\Jobs\BroadcastQuestion;
use App\Models\Campaign;
use App\Models\Contact;
use App\Services\CampaignMetrics;
use App\Services\Incoming\IncomingMessageHandler;
use App\Services\Messaging\FakeMessagingChannel;
use App\Services\Messaging\MessagingManager;
use App\Services\Messaging\OutboundRecorder;
use App\Support\IncomingUpdate;
use App\Support\MessageCopy;
use App\Support\Phone;
use App\Support\Presenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CampaignController extends Controller
{
    use ResolvesNewsroom;

    public function index(): Response
    {
        $campaigns = $this->newsroom()->campaigns()
            ->withCount([
                'messages as replies_count' => function ($query): void {
                    $query->where('direction', 'in');
                },
                'messages as voice_count' => function ($query): void {
                    $query->where('direction', 'in')->where('type', 'audio');
                },
            ])
            ->withExists([
                'messages as has_test_data' => function ($query): void {
                    $query->where('is_test_data', true);
                },
            ])
            ->latest()
            ->get()
            ->map(fn (Campaign $campaign): array => Presenter::campaign($campaign))
            ->values()
            ->all();

        return Inertia::render('campaigns/index', [
            'campaigns' => $campaigns,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('campaigns/create', [
            'neighborhoods' => config('dalil.neighborhoods'),
            'newsroomName' => $this->newsroom()->name,
        ]);
    }

    public function store(StoreCampaignRequest $request): RedirectResponse
    {
        $newsroom = $this->newsroom();
        $neighborhoods = $request->input('neighborhoods', []);

        $campaign = Campaign::query()->create([
            'newsroom_id' => $newsroom->id,
            'user_id' => $request->user()?->id,
            'title' => $request->string('title')->toString(),
            'question' => $request->string('question')->toString(),
            'template_name' => $request->string('template_name')->toString() ?: 'daily_question',
            'template_lang' => $request->string('template_lang')->toString() ?: 'ar',
            'closes_at' => $request->input('closes_at'),
            'audience' => [
                'neighborhoods' => is_array($neighborhoods) ? array_values($neighborhoods) : [],
            ],
        ]);

        if ($request->boolean('send')) {
            BroadcastQuestion::dispatch($campaign);
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Question queued for opted-in subscribers.']);
        } else {
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Draft saved.']);
        }

        return to_route('campaigns.show', $campaign);
    }

    public function show(Campaign $campaign, CampaignMetrics $metrics): Response
    {
        $campaign = $this->ownedCampaign($campaign);

        return Inertia::render('campaigns/show', [
            'campaign' => Presenter::campaign($campaign, $metrics->summarize($campaign)),
            'broadcasts' => $campaign->broadcasts()->latest()->get()->map(fn ($broadcast) => Presenter::broadcast($broadcast))->values()->all(),
            'canSimulate' => app()->environment('local', 'testing') || config('dalil.drivers.messaging') === 'fake',
            'neighborhoods' => config('dalil.neighborhoods'),
            'drivers' => [
                'messaging' => (string) config('dalil.drivers.messaging'),
                'transcription' => (string) config('dalil.drivers.transcription'),
            ],
        ]);
    }

    public function send(Campaign $campaign): RedirectResponse
    {
        $campaign = $this->ownedCampaign($campaign);
        BroadcastQuestion::dispatch($campaign);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Question queued.']);

        return back();
    }

    public function testSend(Request $request, Campaign $campaign, MessagingManager $messaging, OutboundRecorder $recorder): RedirectResponse
    {
        $campaign = $this->ownedCampaign($campaign);
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:32'],
        ]);

        $phone = Phone::normalize($data['phone']);
        $driver = (string) config('dalil.drivers.messaging');
        $allowed = array_filter(array_map(
            fn (string $number): string => Phone::normalize($number),
            explode(',', (string) config('services.whatsapp.test_recipients')),
        ));

        if ($driver !== 'fake' && ! in_array($phone, $allowed, true)) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Add this number to WHATSAPP_TEST_RECIPIENTS first.']);

            return back();
        }

        $contact = Contact::fromSender($this->newsroom(), $phone, ContactChannel::WhatsApp);

        if (! $contact->canReceive()) {
            if ($driver !== 'fake') {
                Inertia::flash('toast', ['type' => 'error', 'message' => 'This number has not sent JOIN.']);

                return back();
            }

            $version = config('dalil.consent_text_version');
            $contact->forceFill([
                'consent_at' => now(),
                'consent_source' => 'web',
                'consent_text_version' => is_string($version) ? $version : '2026-10-24',
                'is_test_data' => true,
            ])->save();
        }

        $newsroom = $campaign->newsroom ?? $this->newsroom();
        $text = MessageCopy::question($newsroom->name, $campaign->question);
        $channel = $messaging->forContact($contact);
        $result = $channel->sendTemplate(
            $contact->wa_id,
            $campaign->template_name ?: 'daily_question',
            $campaign->template_lang ?: 'ar',
            [$newsroom->name, $campaign->question],
            $text,
        );
        $recorder->store($contact, $campaign, null, $result, MessageType::Text, $text);

        Inertia::flash('toast', [
            'type' => $result->ok ? 'success' : 'error',
            'message' => $result->ok ? 'Test message sent.' : 'Test message failed.',
        ]);

        return back();
    }

    public function simulate(Request $request, Campaign $campaign, IncomingMessageHandler $handler): RedirectResponse
    {
        abort_unless(
            app()->environment('local', 'testing') || config('dalil.drivers.messaging') === 'fake',
            404,
        );

        $campaign = $this->ownedCampaign($campaign);
        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'type' => ['required', 'in:text,audio'],
            'neighborhood' => ['nullable', 'string', 'max:80'],
        ]);

        if ($data['type'] === 'audio' && config('dalil.drivers.transcription') !== 'fake') {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Simulated voice notes need TRANSCRIPTION_DRIVER=fake. Use a real WhatsApp voice note with OpenAI.',
            ]);

            return back();
        }

        $sender = '2126'.random_int(10000000, 99999999);
        $newsroom = $this->newsroom();
        $contact = Contact::fromSender($newsroom, $sender, ContactChannel::WhatsApp);
        $version = config('dalil.consent_text_version');
        $contact->forceFill([
            'neighborhood' => $data['neighborhood'] ?: 'سيدي مومن',
            'consent_at' => now(),
            'consent_source' => 'join',
            'consent_text_version' => is_string($version) ? $version : '2026-10-24',
            'is_test_data' => true,
        ])->save();

        $mediaId = null;

        if ($data['type'] === 'audio') {
            $mediaId = 'sim'.str_replace('-', '', (string) Str::uuid());
            Storage::disk('local')->put(FakeMessagingChannel::storagePath($mediaId).'.txt', $data['body']);
        }

        $handler->handle($newsroom, new IncomingUpdate(
            kind: 'message',
            channel: 'whatsapp',
            phoneNumberId: $newsroom->wa_phone_number_id,
            externalId: 'sim.'.Str::uuid()->toString(),
            senderId: $sender,
            senderName: null,
            type: $data['type'],
            body: $data['type'] === 'text' ? $data['body'] : null,
            mediaId: $mediaId,
            duration: $data['type'] === 'audio' ? 18 : null,
            status: null,
            statusMessageId: null,
            choiceId: null,
            timestamp: now()->getTimestamp(),
        ));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Simulated reply stored.']);

        return to_route('campaigns.inbox', $campaign);
    }
}
