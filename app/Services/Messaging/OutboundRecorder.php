<?php

namespace App\Services\Messaging;

use App\Enums\MessageDirection;
use App\Enums\MessageType;
use App\Models\Broadcast;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Message;
use App\Support\OutboundResult;

class OutboundRecorder
{
    public function store(
        Contact $contact,
        ?Campaign $campaign,
        ?Broadcast $broadcast,
        OutboundResult $result,
        MessageType $type,
        string $body,
    ): Message {
        return Message::query()->create([
            'campaign_id' => $campaign?->id,
            'contact_id' => $contact->id,
            'broadcast_id' => $broadcast?->id,
            'wa_message_id' => $result->externalId,
            'direction' => MessageDirection::Out,
            'type' => $type,
            'body' => $body,
            'status' => $result->ok ? 'sent' : 'failed',
            'is_test_data' => (bool) $contact->is_test_data,
            'received_at' => now(),
        ]);
    }
}
