<?php

namespace App\External\MockApiPlate\Dto;

use OpenApi\Attributes as OA;

/**
 * Structure de réponse renvoyée par la mock API d'immatriculation.
 * Toutes les propriétés sauf "plate" sont optionnelles afin de permettre
 * les retours de véhicules non trouvés.
 */
#[OA\Schema(
    title: "PlateLookupResponseDto",
    description: "Structured response for mock license plate lookup"
)]
class PlateLookupResponseDto
{
    /**
     * La plaque d'immatriculation recherchée (toujours présente dans la réponse).
     */
    #[OA\Property(type: "string", example: "AA123BB")]
    public string $plate;

    #[OA\Property(type: "string", example: "VF15ABHG854895231", nullable: true)]
    public ?string $vin;

    #[OA\Property(type: "string", example: "Renault", nullable: true)]
    public ?string $brand;

    #[OA\Property(type: "string", example: "Clio", nullable: true)]
    public ?string $model;

    #[OA\Property(type: "string", example: "1.5 DCI", nullable: true)]
    public ?string $version;

    #[OA\Property(type: "string", example: "Diesel", nullable: true)]
    public ?string $energy;

    #[OA\Property(type: "integer", example: 85, nullable: true)]
    public ?int $horsePower;

    #[OA\Property(type: "number", format: "float", example: 4.0, nullable: true)]
    public ?float $fiscalPower;

    #[OA\Property(type: "string", example: "Manuelle", nullable: true)]
    public ?string $gearBox;

    #[OA\Property(type: "integer", example: 5, nullable: true)]
    public ?int $doors;

    #[OA\Property(type: "integer", example: 5, nullable: true)]
    public ?int $seats;

    #[OA\Property(type: "string", example: "Citadine", nullable: true)]
    public ?string $bodyType;

    #[OA\Property(type: "integer", example: 1050, nullable: true)]
    public ?int $weightKg;

    #[OA\Property(type: "string", example: "Rouge", nullable: true)]
    public ?string $color;

    #[OA\Property(type: "integer", example: 120000, nullable: true)]
    public ?int $mileage;

    #[OA\Property(type: "string", format: "date", example: "2016-05-12", nullable: true)]
    public ?string $registrationDate;

    /**
     * DTO immuable : toutes les valeurs sont fournies au constructeur.
     */
    public function __construct(
        string $plate,
        ?string $vin = null,
        ?string $brand = null,
        ?string $model = null,
        ?string $version = null,
        ?string $energy = null,
        ?int $horsePower = null,
        ?float $fiscalPower = null,
        ?string $gearBox = null,
        ?int $doors = null,
        ?int $seats = null,
        ?string $bodyType = null,
        ?int $weightKg = null,
        ?string $color = null,
        ?int $mileage = null,
        ?string $registrationDate = null
    ) {
        $this->plate = strtoupper($plate);
        $this->vin = $vin;
        $this->brand = $brand;
        $this->model = $model;
        $this->version = $version;
        $this->energy = $energy;
        $this->horsePower = $horsePower;
        $this->fiscalPower = $fiscalPower;
        $this->gearBox = $gearBox;
        $this->doors = $doors;
        $this->seats = $seats;
        $this->bodyType = $bodyType;
        $this->weightKg = $weightKg;
        $this->color = $color;
        $this->mileage = $mileage;
        $this->registrationDate = $registrationDate;
    }
}
