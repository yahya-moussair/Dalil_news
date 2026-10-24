<?php

namespace App\Services\Incoming;

use App\Support\IncomingUpdate;
use App\Support\Payload;

class WhatsAppPayloadParser
{
    /**
     * @param  array<string, mixed>  $payload
     * @return list<IncomingUpdate>
     */
    public function parse(array $payload): array
    {
        $updates = [];

        foreach (Payload::listOfAssoc($payload['entry'] ?? null) as $entry) {
            foreach (Payload::listOfAssoc($entry['changes'] ?? null) as $change) {
                $value = $change['value'] ?? null;

                if (! is_array($value)) {
                    continue;
                }

                $value = Payload::assoc($value);
                $metadata = is_array($value['metadata'] ?? null) ? Payload::assoc($value['metadata']) : [];
                $phoneNumberId = Payload::str($metadata['phone_number_id'] ?? null);
                $names = [];

                foreach (Payload::listOfAssoc($value['contacts'] ?? null) as $contact) {
                    $waId = Payload::str($contact['wa_id'] ?? null);
                    $profile = is_array($contact['profile'] ?? null) ? Payload::assoc($contact['profile']) : [];
                    $name = Payload::str($profile['name'] ?? null);

                    if ($waId !== null && $name !== null) {
                        $names[$waId] = $name;
                    }
                }

                foreach (Payload::listOfAssoc($value['statuses'] ?? null) as $status) {
                    $updates[] = new IncomingUpdate(
                        kind: 'status',
                        channel: 'whatsapp',
                        phoneNumberId: $phoneNumberId,
                        externalId: null,
                        senderId: Payload::str($status['recipient_id'] ?? null),
                        senderName: null,
                        type: null,
                        body: null,
                        mediaId: null,
                        duration: null,
                        status: Payload::str($status['status'] ?? null),
                        statusMessageId: Payload::str($status['id'] ?? null),
                        choiceId: null,
                        timestamp: Payload::int($status['timestamp'] ?? null),
                    );
                }

                foreach (Payload::listOfAssoc($value['messages'] ?? null) as $message) {
                    $from = Payload::str($message['from'] ?? null);
                    $type = Payload::str($message['type'] ?? null) ?? 'text';
                    $body = null;
                    $mediaId = null;
                    $duration = null;
                    $choiceId = null;
                    $normalizedType = 'text';

                    if ($type === 'text') {
                        $text = is_array($message['text'] ?? null) ? Payload::assoc($message['text']) : [];
                        $body = Payload::str($text['body'] ?? null);
                    } elseif ($type === 'audio') {
                        $audio = is_array($message['audio'] ?? null) ? Payload::assoc($message['audio']) : [];
                        $mediaId = Payload::str($audio['id'] ?? null);
                        $normalizedType = 'audio';
                    } elseif ($type === 'interactive') {
                        $interactive = is_array($message['interactive'] ?? null) ? Payload::assoc($message['interactive']) : [];
                        $reply = $interactive['list_reply'] ?? $interactive['button_reply'] ?? null;
                        $reply = is_array($reply) ? Payload::assoc($reply) : [];
                        $choiceId = Payload::str($reply['id'] ?? null);
                        $body = Payload::str($reply['title'] ?? null);
                        $normalizedType = 'interactive';
                    } else {
                        continue;
                    }

                    $updates[] = new IncomingUpdate(
                        kind: 'message',
                        channel: 'whatsapp',
                        phoneNumberId: $phoneNumberId,
                        externalId: Payload::str($message['id'] ?? null),
                        senderId: $from,
                        senderName: $from !== null ? ($names[$from] ?? null) : null,
                        type: $normalizedType,
                        body: $body,
                        mediaId: $mediaId,
                        duration: $duration,
                        status: null,
                        statusMessageId: null,
                        choiceId: $choiceId,
                        timestamp: Payload::int($message['timestamp'] ?? null),
                    );
                }
            }
        }

        return $updates;
    }
}
