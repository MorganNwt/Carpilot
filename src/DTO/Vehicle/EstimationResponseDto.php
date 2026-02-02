<?php

namespace App\DTO\Vehicle;

use DateTimeImmutable;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: "Estimation Response",
    description: "Detailed information about a vehicle. Returned after creation or when fetching details."
)]
class EstimationResponseDto
{
    public function __construct(
        #[OA\Property(example: 7)]
        public readonly int $id,

        #[OA\Property(description: "Estimation status")]
        public readonly string $status,

        #[OA\Property(description: "The vehicle's estimated price")]
        public readonly float $estimated_price,

        #[OA\Property(description: "The date and time the estimation was created")]
        public readonly ?DateTimeImmutable $createdAt,

        #[OA\Property(description: "The vehicle's offered price")]
        public readonly ?float $offer_price = null,

        #[OA\Property(description: "Whether seller can edit offer price (derived from status)")]
        public readonly bool $can_edit_offer = false,
    ) {}
}
