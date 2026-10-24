<?php

namespace App\Support;

final class OutboundResult
{
    private function __construct(
        public bool $ok,
        public ?string $externalId,
        public ?string $error,
        public string $body,
    ) {}

    public static function ok(?string $externalId, string $body): self
    {
        return new self(true, $externalId, null, $body);
    }

    public static function failed(string $error, string $body = ''): self
    {
        $trimmed = mb_substr($error, 0, 500);

        return new self(false, null, $trimmed, $body);
    }
}
