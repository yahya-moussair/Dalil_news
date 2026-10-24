<?php

namespace Database\Seeders;

use App\Enums\BroadcastKind;
use App\Enums\CampaignStatus;
use App\Enums\ContactChannel;
use App\Enums\MessageDirection;
use App\Enums\MessageType;
use App\Enums\UserRole;
use App\Models\Broadcast;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Message;
use App\Models\Newsroom;
use App\Models\Topic;
use App\Models\Transcript;
use App\Models\User;
use App\Support\Phone;
use App\Support\TopicLexicon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DemoRepliesSeeder extends Seeder
{
    public function run(): void
    {
        $phoneNumberId = config('services.whatsapp.phone_number_id');

        $newsroom = Newsroom::query()->create([
            'name' => 'كازا حيّ',
            'city' => 'الدار البيضاء',
            'wa_phone_number_id' => is_string($phoneNumberId) && $phoneNumberId !== '' ? $phoneNumberId : null,
            'plan' => 'pilot',
            'settings' => [
                'display_number' => (string) (config('services.whatsapp.display_number') ?: '212520000111'),
                'latin_name' => 'Casa Hay',
            ],
        ]);

        foreach ([
            ['Hafsa Ouahbi', 'hafsa@casahay.test'],
            ['Yahya Moussair', 'yahya@casahay.test'],
        ] as [$name, $email]) {
            User::query()->create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password'),
                'newsroom_id' => $newsroom->id,
                'role' => UserRole::Admin,
                'email_verified_at' => now(),
            ]);
        }

        $author = User::query()->where('email', 'hafsa@casahay.test')->firstOrFail();
        $neighborhoods = [
            'سيدي مومن', 'البرنوصي', 'عين السبع', 'مولاي رشيد', 'الحي المحمدي',
            'سباتة', 'الحي الحسني', 'درب السلطان', 'عين الشق', 'المعاريف',
        ];

        /** @var array<string, list<Contact>> $byNeighborhood */
        $byNeighborhood = [];

        for ($i = 1; $i <= 320; $i++) {
            $neighborhood = $neighborhoods[$i % count($neighborhoods)];
            $phone = '2126'.str_pad((string) (10000000 + $i), 8, '0', STR_PAD_LEFT);
            $optedOut = $i > 312;
            $contact = Contact::query()->create([
                'newsroom_id' => $newsroom->id,
                'wa_id' => Phone::normalize($phone),
                'wa_id_hash' => Phone::hash(Phone::normalize($phone)),
                'neighborhood' => $neighborhood,
                'language_pref' => ['ary', 'ar', 'fr'][$i % 3],
                'consent_at' => now()->subDays(12 + ($i % 20)),
                'consent_source' => $i % 10 === 0 ? 'web' : ($i % 3 === 0 ? 'join' : 'qr'),
                'consent_text_version' => '2026-10-24',
                'opted_out_at' => $optedOut ? now()->subDay() : null,
                'channel' => $i % 20 === 0 ? ContactChannel::Telegram : ContactChannel::WhatsApp,
                'is_test_data' => true,
            ]);

            if (! $optedOut) {
                $byNeighborhood[$neighborhood][] = $contact;
            }
        }

        $campaign = Campaign::query()->create([
            'newsroom_id' => $newsroom->id,
            'user_id' => $author->id,
            'title' => 'أكبر مشكل في الحي — أكتوبر 2026',
            'question' => 'ما هو أكبر مشكل كتعيشوه فالحي ديالكم؟',
            'template_name' => 'daily_question',
            'template_lang' => 'ar',
            'status' => CampaignStatus::Sent,
            'sent_at' => now()->subHours(20),
            'closes_at' => now()->addDays(4),
        ]);

        Broadcast::query()->create([
            'campaign_id' => $campaign->id,
            'kind' => BroadcastKind::Question,
            'template_name' => 'daily_question',
            'total' => 312,
            'sent' => 312,
            'delivered' => 286,
            'read' => 201,
            'failed' => 0,
            'cost_estimate' => round(312 * 0.0414, 4),
        ]);

        $corpus = DemoCorpus::messages();
        $start = now()->subHours(18);
        $buckets = [];
        $cursors = [];

        foreach ($corpus as $index => $item) {
            $pool = $byNeighborhood[$item['neighborhood']] ?? [];

            if ($pool === []) {
                $pool = $byNeighborhood['سيدي مومن'];
            }

            $cursor = $cursors[$item['neighborhood']] ?? 0;
            $contact = $pool[$cursor % count($pool)];
            $cursors[$item['neighborhood']] = $cursor + 1;
            $received = $start->copy()->addMinutes($index * 5);
            $type = $item['audio'] ? MessageType::Audio : MessageType::Text;
            $voicePath = $type === MessageType::Audio ? $this->demoVoicePath() : null;

            $message = Message::query()->create([
                'campaign_id' => $campaign->id,
                'contact_id' => $contact->id,
                'wa_message_id' => 'demo.'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'direction' => MessageDirection::In,
                'type' => $type,
                'body' => $type === MessageType::Text ? $item['text'] : null,
                'media_id' => $type === MessageType::Audio ? 'demo-audio-'.($index + 1) : null,
                'media_path' => $voicePath,
                'duration_sec' => $type === MessageType::Audio ? 12 + ($index % 28) : null,
                'status' => 'received',
                'is_test_data' => true,
                'received_at' => $received,
            ]);

            Transcript::query()->create([
                'message_id' => $message->id,
                'raw_text' => $item['text'],
                'normalized_text' => $item['normalized'],
                'summary_msa' => $item['summary'],
                'language' => $item['language'],
                'provider' => 'demo-seed',
                'model' => 'demo-corpus',
                'confidence' => 0.9,
            ]);

            if ($contact->last_inbound_at === null || $received->greaterThan($contact->last_inbound_at)) {
                $contact->forceFill(['last_inbound_at' => $received])->save();
            }

            $buckets[$item['topic']][] = [
                'message' => $message,
                'featured' => $item['featured'],
            ];
        }

        $definitions = [];

        foreach (TopicLexicon::definitions() as $definition) {
            $definitions[$definition['key']] = $definition;
        }

        foreach ($buckets as $key => $rows) {
            $definition = $definitions[$key];
            $topic = Topic::query()->create([
                'campaign_id' => $campaign->id,
                'label_ar' => $definition['label_ar'],
                'label_fr' => $definition['label_fr'],
                'summary' => $definition['summary'],
                'replies_count' => count($rows),
                'is_locked' => false,
            ]);

            $attach = [];

            foreach ($rows as $row) {
                $attach[$row['message']->id] = [
                    'score' => $row['featured'] ? 1 : 0.6,
                    'is_featured_quote' => $row['featured'],
                ];
            }

            $topic->messages()->attach($attach);
        }

        $campaign->forceFill(['clustered_at' => now()])->save();
    }

    private function demoVoicePath(): string
    {
        $path = 'voice-notes/demo-voice.wav';

        if (! Storage::disk('local')->exists($path)) {
            Storage::disk('local')->put($path, $this->toneWav());
        }

        return $path;
    }

    private function toneWav(): string
    {
        $sampleRate = 8000;
        $samples = (int) ($sampleRate * 0.8);
        $pcm = '';

        for ($i = 0; $i < $samples; $i++) {
            $value = (int) round(sin(2 * M_PI * 440 * ($i / $sampleRate)) * 9000);
            $pcm .= pack('s', $value);
        }

        $dataSize = strlen($pcm);

        return 'RIFF'
            .pack('V', 36 + $dataSize)
            .'WAVE'
            .'fmt '
            .pack('VvvVVvv', 16, 1, 1, $sampleRate, $sampleRate * 2, 2, 16)
            .'data'
            .pack('V', $dataSize)
            .$pcm;
    }
}
