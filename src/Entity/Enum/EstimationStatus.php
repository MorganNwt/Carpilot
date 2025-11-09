<?php

namespace App\Entity\Enum;

enum EstimationStatus:string
{
    case Estimated = 'estimated';
    case OfferMade = 'offer_made';
    case TransactionCompleted = 'transaction_completed';
    case Cancelled = 'cancelled';
}


