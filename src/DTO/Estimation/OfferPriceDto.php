<?php

namespace App\DTO\Estimation;

use Symfony\Component\Validator\Constraints as Assert;

class OfferPriceDto
{
    #[Assert\NotNull]
    #[Assert\Positive]
    public float $offer_price;
}
