<?php

namespace App\Support;

final class IncomingUpdate
{
    public function __construct(
        public string $kind,
        public string $channel,
        public ?string $phoneNumberId,
        public ?string $externalId,
        public ?string $senderId,
        public ?string $senderName,
        public ?string $type,
        public ?string $body,
        public ?string $mediaId,
        public ?int $duration,
        public ?string $status,
        public ?string $statusMessageId,
        public ?string $choiceId,
        public ?int $timestamp,
    ) {}
}
