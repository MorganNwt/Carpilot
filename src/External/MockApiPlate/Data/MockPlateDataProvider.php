<?php

namespace App\External\MockApiPlate\Data;

use App\External\MockApiPlate\Dto\PlateLookupResponseDto;
use App\External\MockApiPlate\Mapper\PlateLookupMapper;

/**
 * Simule un fournisseur de données véhicule à partir d’une plaque.
 * Ce service agit comme un client d’API fictive avec une flotte en mémoire.
 */
class MockPlateDataProvider
{
    public function findByPlate($plate)
    {
        foreach ($this->getFleet() as $vehicle) {
            if ($vehicle['plate'] === $plate) {
                return PlateLookupMapper::fromArray($vehicle);
            }
        }

        return PlateLookupMapper::fromArray(['plate' => $plate]);
    }

    /**
     * Flotte simulée de véhicules.
     */
    private function getFleet(): array
    {
        return [
            [
                'plate' => 'AA123AA',
                'vin' => 'VF15ABHG854895231',
                'brand' => 'RENAULT',
                'model' => 'CLIO',
                'version' => '1.5 DCI',
                'energy' => 'DIESEL',
                'horsePower' => 85,
                'fiscalPower' => 4,
                'gearBox' => 'MANUELLE',
                'doors' => 5,
                'seats' => 5,
                'bodyType' => 'CITADINE',
                'weightKg' => 1050,
                'color' => 'ROUGE',
                'registrationDate' => '2016-05-12'
            ],
            [
                'plate' => 'BB456BB',
                'vin' => 'VF35EBHG854895232',
                'brand' => 'PEUGEOT',
                'model' => '208',
                'version' => 'PURETECH 100',
                'energy' => 'ESSENCE',
                'horsePower' => 100,
                'fiscalPower' => 5,
                'gearBox' => 'MANUELLE',
                'doors' => 5,
                'seats' => 5,
                'bodyType' => 'CITADINE',
                'weightKg' => 980,
                'color' => 'BLEU',
                'registrationDate' => '2019-07-23'
            ]
        ];
    }
}