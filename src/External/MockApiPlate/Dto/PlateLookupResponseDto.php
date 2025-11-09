<?php

namespace App\External\MockApiPlate\Dto;

use OpenApi\Attributes as OA;

/**
 * Représente la réponse structurée retournée par la mock API d'immatriculation.
 *
 */
#[OA\Schema(
    title: "PlateLookupResponseDto",
    description: "Structured response for mock license plate lookup"
)]
class PlateLookupResponseDto
{
    /**
     * @param string|null $plate La plaque d'immatriculation.
     * @param string|null $vin Le numéro d'identification du véhicule.
     * @param string|null $brand La marque du véhicule.
     * @param string|null $model Le modèle du véhicule.
     * @param string|null $version La version spécifique du véhicule.
     * @param string|null $energy Le type d'énergie.
     * @param int|null $horsePower La puissance en chevaux.
     * @param float|null $fiscalPower La puissance fiscale.
     * @param string|null $gearBox Le type de boîte de vitesse.
     * @param int|null $doors Le nombre de portes.
     * @param int|null $seats Le nombre de sièges.
     * @param string|null $bodyType Le type de carrosserie.
     * @param int|null $weightKg Le poids en kilogrammes.
     * @param string|null $color La couleur du véhicule.
     * @param string|null $registrationDate La date de première immatriculation.
     */
    public function __construct(
        #[OA\Property(type: "string", example: "AA123BB")]
        public string $plate,

        #[OA\Property(type: "string", example: "VF15ABHG854895231")]
        public ?string $vin = null,

        #[OA\Property(type: "string", example: "Renault")]
        public ?string $brand = null,

        #[OA\Property(type: "string", example: "Clio")]
        public ?string $model = null,

        #[OA\Property(type: "string", example: "1.5 DCI")]
        public ?string $version = null,

        #[OA\Property(type: "string", example: "Diesel")]
        public ?string $energy = null,

        #[OA\Property(type: "integer", example: 85)]
        public ?int $horsePower = null,

        #[OA\Property(type: "number", format: "float", example: 4.0)]
        public ?float $fiscalPower = null,

        #[OA\Property(type: "string", example: "Manuelle")]
        public ?string $gearBox = null,
        #[OA\Property(type: "integer", example: 5)]
        public ?int $doors = null,

        #[OA\Property(type: "integer", example: 5)]
        public ?int $seats = null,

        #[OA\Property(type: "string", example: "Citadine")]
        public ?string $bodyType = null,

        #[OA\Property(type: "integer", example: 1050)]
        public ?int $weightKg = null,

        #[OA\Property(type: "string", example: "Rouge")]
        public ?string $color = null,

        #[OA\Property(type: "string", format: "date", example: "2016-05-12")]
        public ?string $registrationDate = null
    ) {
    }
}