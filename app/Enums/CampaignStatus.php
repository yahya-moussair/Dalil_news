<?php

namespace App\Enums;

enum CampaignStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Voting = 'voting';
    case Closed = 'closed';
}
