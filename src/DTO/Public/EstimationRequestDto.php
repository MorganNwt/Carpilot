<?php

namespace App\DTO\Public;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(
    title: "",
    description: ""
)]
class EstimationRequestDto
{
    #[OA\Property(description: "The vehicle's license plate.", example: "AA-123-BB")]
    #[Assert\NotBlank(message: "The license plate cannot be blank.")]
    public ?string $plate = null;

    #[OA\Property(description: "The unique 17-character Vehicle Identification Number.", example: "VF15ABHG854895231")]
    #[Assert\NotBlank(message: "The VIN cannot be blank.")]
    #[Assert\Length(exactly: 17, exactMessage: "The VIN must be exactly {{ limit }} characters long.")]
    public ?string $vin = null;

    #[OA\Property(description: "Brand of the vehicle.", example: "Renault")]
    #[Assert\NotBlank]
    public ?string $brand = null;

    #[OA\Property(description: "Model of the vehicle.", example: "Clio")]
    #[Assert\NotBlank]
    public ?string $model = null;

    #[OA\Property(description: "Specific version or trim of the model.", example: "1.5 DCI Intens")]
    public ?string $version = null;

    #[OA\Property(description: "Type of energy used by the vehicle.", example: "Diesel")]
    #[Assert\NotBlank]
    public ?string $energy = null;

    #[OA\Property(description: "Engine power in horsepower.", example: 90)]
    #[Assert\NotBlank]
    #[Assert\Positive(message: "Horsepower must be a positive number.")]
    public ?int $horsePower = null;

    #[OA\Property(description: "Fiscal power of the vehicle.", example: 5.0)]
    #[Assert\NotBlank]
    #[Assert\Positive(message: "Fiscal power must be a positive number.")]
    public ?float $fiscalPower = null;

    #[OA\Property(description: "Type of gearbox.", example: "Manuelle")]
    #[Assert\NotBlank]
    public ?string $gearBox = null;

    #[OA\Property(description: "Number of doors.", example: 5)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?int $doors = null;

    #[OA\Property(description: "Number of seats.", example: 5)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?int $seats = null;

    #[OA\Property(description: "The vehicle's body type (e.g., Berline, SUV).", example: "Berline")]
    #[Assert\NotBlank]
    public ?string $bodyType = null;

    #[OA\Property(description: "Weight of the vehicle in kilograms.", example: 1178)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?int $weightKg = null;

    #[OA\Property(description: "Color of the vehicle.", example: "Bleu Iron")]
    #[Assert\NotBlank]
    public ?string $color = null;

    #[OA\Property(description: "The first registration date of the vehicle.", example: "2019-07-23")]
    #[Assert\NotBlank(message: "The registration date is required.")]
    #[Assert\Date(message: "The registration date format is invalid. Please use YYYY-MM-DD.")]
    public ?string $registrationDate = null;

    public ?int $mileage;
}