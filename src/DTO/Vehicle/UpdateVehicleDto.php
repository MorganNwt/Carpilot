<?php

namespace App\DTO\Vehicle;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateVehicleDto
{
    #[OA\Property(description: "The vehicle's license plate.", example: "AA-123-BB")]
    public ?string $plate = null;

    #[OA\Property(description: "The unique 17-character Vehicle Identification Number.", example: "VF15ABHG854895231")]
    #[Assert\Length(exactly: 17, exactMessage: "The VIN must be exactly {{ limit }} characters long.")]
    public ?string $vin = null;

    #[OA\Property(description: "Brand of the vehicle.", example: "Renault")]
    public ?string $brand = null;
    #[OA\Property(description: "Model of the vehicle.", example: "Clio")]

    public ?string $model = null;
    #[OA\Property(description: "Specific version or trim of the model.", example: "1.5 DCI Intens")]
    public ?string $version = null;

    #[OA\Property(description: "Type of energy used by the vehicle.", example: "Diesel")]
    public ?string $energy = null;

    #[OA\Property(description: "Engine power in horsepower.", example: 90)]
    #[Assert\Positive(message: "Horsepower must be a positive number.")]
    public ?int $horsePower = null;

    #[OA\Property(description: "Fiscal power of the vehicle.", example: 5.0)]
    #[Assert\Positive(message: "Fiscal power must be a positive number.")]
    public ?float $fiscalPower = null;

    #[OA\Property(description: "Type of gearbox.", example: "Manuelle")]
    public ?string $gearBox = null;

    #[OA\Property(description: "Number of doors.", example: 5)]
    #[Assert\Positive]
    public ?int $doors = null;

    #[OA\Property(description: "Number of seats.", example: 5)]
    #[Assert\Positive]
    public ?int $seats = null;

    #[OA\Property(description: "The vehicle's body type (e.g., Berline, SUV).", example: "Berline")]
    public ?string $bodyType = null;

    #[OA\Property(description: "Weight of the vehicle in kilograms.", example: 1178)]

    #[Assert\Positive]
    public ?int $weightKg = null;

    #[OA\Property(description: "Color of the vehicle.", example: "Bleu Iron")]
    public ?string $color = null;

    #[OA\Property(description: "The first registration date of the vehicle.", example: "2019-07-23")]
    #[Assert\Date(message: "The registration date format is invalid. Please use YYYY-MM-DD.")]
    public ?string $registrationDate = null;
}