<?php

namespace App\Enum;

enum EstimationStatus: string
{
    case ESTIMATED = 'estimated';
    case OFFER_MADE = 'offer_made';
    case TRANSACTION_COMPLETED = 'transaction_completed';
    case CANCELLED = 'cancelled';
}
