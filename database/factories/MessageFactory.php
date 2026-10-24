<?php

namespace Database\Factories;

use App\Enums\MessageDirection;
use App\Enums\MessageType;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    protected $model = Message::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'contact_id' => Contact::factory(),
            'wa_message_id' => 'wamid.'.Str::uuid()->toString(),
            'direction' => MessageDirection::In,
            'type' => MessageType::Text,
            'body' => 'الما كيتقطع فسيدي مومن',
            'status' => 'received',
            'is_test_data' => false,
            'received_at' => now(),
        ];
    }
}
