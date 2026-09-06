<?php

namespace App\DTO\Public;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(
    title: "Estimation Request",
    description: "Data required to calculate a vehicle estimation."
)]
class EstimationRequestDto
{
    #[OA\Property(example: "AA-123-BB")]
    #[Assert\NotBlank(message: "The license plate cannot be blank.")]
    public string $plate;

    #[OA\Property(example: "VF15ABHG854895231")]
    #[Assert\NotBlank(message: "The VIN cannot be blank.")]
    #[Assert\Length(
        exactly: 17,
        exactMessage: "The VIN must be exactly {{ limit }} characters long."
    )]
    public string $vin;

    #[OA\Property(example: "Renault")]
    #[Assert\NotBlank]
    public string $brand;

    #[OA\Property(example: "Clio")]
    #[Assert\NotBlank]
    public string $model;

    #[OA\Property(example: "1.5 DCI Intens", nullable: true)]
    public ?string $version = null;

    #[OA\Property(example: "Diesel")]
    #[Assert\NotBlank]
    public string $energy;

    #[OA\Property(example: 90)]
    #[Assert\Positive(message: "Horsepower must be a positive number.")]
    public int $horsePower;

    #[OA\Property(example: 5)]
    #[Assert\Positive(message: "Fiscal power must be a positive number.")]
    public float $fiscalPower;

    #[OA\Property(example: "Manuelle")]
    #[Assert\NotBlank]
    public string $gearBox;

    #[OA\Property(example: 5)]
    #[Assert\Positive]
    public int $doors;

    #[OA\Property(example: 5)]
    #[Assert\Positive]
    public int $seats;

    #[OA\Property(example: "Berline")]
    #[Assert\NotBlank]
    public string $bodyType;

    #[OA\Property(example: 1178)]
    #[Assert\Positive]
    public int $weightKg;

    #[OA\Property(example: "Bleu Iron")]
    #[Assert\NotBlank]
    public string $color;

    #[OA\Property(example: 85000, nullable: true)]
    #[Assert\Positive]
    public ?int $mileage = null;

    #[OA\Property(example: "2019-07-23")]
    #[Assert\NotBlank(message: "The registration date is required.")]
    #[Assert\Date(message: "The registration date format must be YYYY-MM-DD.")]
    public string $registrationDate;
}
