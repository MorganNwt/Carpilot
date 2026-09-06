<?php

namespace App\Enum;

enum EstimationStatus: string
{
    case ESTIMATED = 'estimated';
    case OFFER_MADE = 'offer_made';

    case IN_REVIEW = 'in_review';
    case TRANSACTION_COMPLETED = 'transaction_completed';
    
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
}
