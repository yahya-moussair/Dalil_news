<?php

namespace App\Services\Incoming;

use App\Support\IncomingUpdate;
use App\Support\Payload;

class TelegramPayloadParser
{
    /**
     * @param  array<string, mixed>  $payload
     * @return list<IncomingUpdate>
     */
    public function parse(array $payload): array
    {
        $message = $payload['message'] ?? $payload['edited_message'] ?? null;

        if (! is_array($message)) {
            return [];
        }

        $message = Payload::assoc($message);
        $chat = is_array($message['chat'] ?? null) ? Payload::assoc($message['chat']) : [];
        $from = is_array($message['from'] ?? null) ? Payload::assoc($message['from']) : [];
        $chatId = Payload::str($chat['id'] ?? null);
        $messageId = Payload::int($message['message_id'] ?? null);
        $externalId = $messageId === null ? null : 'tg.'.$messageId;
        $name = Payload::str($from['first_name'] ?? null);

        if (isset($message['voice']) && is_array($message['voice'])) {
            $voice = Payload::assoc($message['voice']);

            return [new IncomingUpdate(
                kind: 'message',
                channel: 'telegram',
                phoneNumberId: null,
                externalId: $externalId,
                senderId: $chatId,
                senderName: $name,
                type: 'audio',
                body: null,
                mediaId: Payload::str($voice['file_id'] ?? null),
                duration: Payload::int($voice['duration'] ?? null),
                status: null,
                statusMessageId: null,
                choiceId: null,
                timestamp: Payload::int($message['date'] ?? null),
            )];
        }

        $text = Payload::str($message['text'] ?? null);

        if ($text === null || $chatId === null) {
            return [];
        }

        return [new IncomingUpdate(
            kind: 'message',
            channel: 'telegram',
            phoneNumberId: null,
            externalId: $externalId,
            senderId: $chatId,
            senderName: $name,
            type: 'text',
            body: $text,
            mediaId: null,
            duration: null,
            status: null,
            statusMessageId: null,
            choiceId: null,
            timestamp: Payload::int($message['date'] ?? null),
        )];
    }
}
