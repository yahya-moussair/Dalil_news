<?php

namespace App\Enums;

enum BroadcastKind: string
{
    case Question = 'question';
    case Vote = 'vote';
    case ReplyBack = 'reply_back';
}
