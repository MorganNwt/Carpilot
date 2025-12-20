<?php

namespace App\DTO\Vehicle;

use App\DTO\Vehicle\EstimationResponseDto;
use DateTimeImmutable;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: "Vehicle Response",
    description: "Detailed information about a vehicle. Returned after creation or when fetching details."
)]
class VehicleResponseDto
{

    public function __construct(
        #[OA\Property(description: "The unique identifier of the vehicle.", example: 12)]
        public readonly int $id,

        #[OA\Property(description: "The vehicle's license plate.", example: "AA-123-BB")]
        public readonly string $plate,

        #[OA\Property(description: "The unique 17-character Vehicle Identification Number.", example: "VF15ABHG854895231")]
        public readonly string $vin,

        #[OA\Property(description: "Brand of the vehicle.", example: "Renault")]
        public readonly string $brand,

        #[OA\Property(description: "Model of the vehicle.", example: "Clio")]
        public readonly string $model,

        #[OA\Property(description: "Specific version or trim of the model.", example: "1.5 DCI Intens", nullable: true)]
        public readonly ?string $version,

        #[OA\Property(description: "Type of energy used by the vehicle.", example: "Diesel")]
        public readonly string $energy,

        #[OA\Property(description: "Engine power in horsepower.", example: 90)]
        public readonly int $horsePower,

        #[OA\Property(description: "The ID of the seller who owns the vehicle.", example: 7)]
        public readonly int $sellerId,

        #[OA\Property(description: "Fiscal power of the vehicle.", example: 5.0)]
        public readonly float $fiscalPower,

        #[OA\Property(description: "Type of gearbox.", example: "Manuelle")]
        public readonly string $gearBox,

        #[OA\Property(description: "Number of doors.", example: 5)]
        public readonly int $doors,

        #[OA\Property(description: "Number of seats.", example: 5)]
        public readonly int $seats,

        #[OA\Property(description: "The vehicle's body type (e.g., Berline, SUV).", example: "Berline", nullable: true)]
        public readonly ?string $bodyType,

        #[OA\Property(description: "Weight of the vehicle in kilograms.", example: 1178, nullable: true)]
        public readonly ?int $weightKg,

        #[OA\Property(description: "Color of the vehicle.", example: "Bleu Iron", nullable: true)]
        public readonly ?string $color,

        #[OA\Property(description: "The vehicle's first registration date.", type: "string", format: "date", example: "2019-10-22")]
        public readonly ?DateTimeImmutable $registrationDate,

        #[OA\Property(description: "The date and time the vehicle was created.", type: "string", format: "date-time")]
        public readonly ?DateTimeImmutable $createdAt,

        #[OA\Property(description: "The date and time the vehicle was last updated.", type: "string", format: "date-time")]
        public readonly ?DateTimeImmutable $updatedAt,

        #[OA\Property(
            description: "Related estimation if exists",
            ref: new OA\Schema(ref: EstimationResponseDto::class),
            nullable: true
        )]
        public readonly ?EstimationResponseDto $estimation = null
    ) {}
}
