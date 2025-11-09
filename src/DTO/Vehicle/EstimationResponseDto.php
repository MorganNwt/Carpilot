<?php

namespace App\DTO\Vehicle;

use DateTimeImmutable;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;


#[OA\Schema(
    title: "Estimation Response",
    description: "Detailed information about a vehicle. Returned after creation or when fetching details."
)]
class EstimationResponseDto
{

    public function __construct(

        public readonly int $id,

        #[OA\Property(description: "")]
        public readonly string $status,

        #[OA\Property(description: "The vehicle's estimated price")]
        public readonly float $estimated_price,

        #[OA\Property(description: "The vehicle's offered price")]
        public readonly ?float $offer_price = null,

        #[OA\Property(description: "The date and time the estimated")]
        public readonly ?DateTimeImmutable $createdAt
        
    ) {
    }
}
