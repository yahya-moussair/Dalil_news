<?php

namespace App\Services\Messaging;

use App\Support\OutboundResult;

interface MessagingChannel
{
    public function name(): string;

    public function sendText(string $to, string $body): OutboundResult;

    /**
     * @param  list<string>  $parameters
     */
    public function sendTemplate(string $to, string $template, string $language, array $parameters, string $fallbackText): OutboundResult;

    /**
     * @param  list<array{id: string, title: string, description: string}>  $rows
     */
    public function sendChoice(string $to, string $body, array $rows): OutboundResult;

    public function downloadMedia(string $mediaId): string;
}
