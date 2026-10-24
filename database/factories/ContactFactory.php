<?php

namespace Database\Factories;

use App\Enums\ContactChannel;
use App\Models\Contact;
use App\Models\Newsroom;
use App\Support\Phone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'newsroom_id' => Newsroom::factory(),
            'wa_id' => '2126'.fake()->unique()->numerify('########'),
            'channel' => ContactChannel::WhatsApp,
            'neighborhood' => 'سيدي مومن',
            'consent_at' => now(),
            'consent_source' => 'join',
            'consent_text_version' => '2026-10-24',
            'is_test_data' => false,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Contact $contact): void {
            $plain = $contact->wa_id;

            if ($plain === '') {
                return;
            }

            $normalized = Phone::normalize($plain);
            $contact->wa_id = $normalized;
            $contact->wa_id_hash = Phone::hash($normalized);
        });
    }
}
