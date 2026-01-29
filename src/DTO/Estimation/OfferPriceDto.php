<?php

namespace App\DTO\Estimation;

use Symfony\Component\Validator\Constraints as Assert;

class OfferPriceDto
{
    // REGEX : "12000", "12000.5", "12000.50"
    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: '/^\d+(\.\d{1,2})?$/',
        message: 'Invalid price format.'
    )]
    public string $offer_price;
}
