<?php

namespace App\External\MockApiPlate\Data;

use App\External\MockApiPlate\Dto\PlateLookupResponseDto;
use App\External\MockApiPlate\Mapper\PlateLookupMapper;

/**
 * Simule un fournisseur de données véhicule basé sur une plaque d'immatriculation.
 * Cette classe représente une API externe fictive, stockant une flotte de véhicules en mémoire.
 */
class MockPlateDataProvider
{
    /** @var array<string, array> Indexation par plaque pour accès rapide */
    private array $fleet;

    public function __construct()
    {
        $this->fleet = $this->loadFleet();
    }

    /**
     * Recherche d'un véhicule par sa plaque.
     * Retourne un DTO complet si trouvé, ou un DTO minimal (véhicule inconnu) sinon.
     */
    public function findByPlate(string $plate): PlateLookupResponseDto
    {
        $plate = strtoupper($plate);

        if (isset($this->fleet[$plate])) {
            return PlateLookupMapper::fromArray($this->fleet[$plate]);
        }

        // Véhicule non trouvé → DTO vide + plaque renseignée
        return PlateLookupMapper::fromArray([
            'plate' => $plate
        ]);
    }

    /**
     * Flotte de véhicules simulée.
     * Indexée par 'plate' pour un accès O(1).
     */
    private function loadFleet(): array
    {
        $vehicles = [
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
                'mileage' => 125000,
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
                'mileage' => 60000,
                'registrationDate' => '2019-07-23'
            ],
            [
                'plate' => 'CC789CC',
                'vin' => 'WVWZZZ1KZ6W854233',
                'brand' => 'VOLKSWAGEN',
                'model' => 'GOLF',
                'version' => '1.6 TDI',
                'energy' => 'DIESEL',
                'horsePower' => 105,
                'fiscalPower' => 6,
                'gearBox' => 'MANUELLE',
                'doors' => 5,
                'seats' => 5,
                'bodyType' => 'COMPACTE',
                'weightKg' => 1220,
                'color' => 'GRIS',
                'mileage' => 90000,
                'registrationDate' => '2015-03-18'
            ],
            [
                'plate' => 'DD321DD',
                'vin' => 'WDB2030461A654321',
                'brand' => 'MERCEDES',
                'model' => 'C200',
                'version' => 'CDI 2.2',
                'energy' => 'DIESEL',
                'horsePower' => 136,
                'fiscalPower' => 7,
                'gearBox' => 'AUTO',
                'doors' => 4,
                'seats' => 5,
                'bodyType' => 'BERLINE',
                'weightKg' => 1480,
                'color' => 'NOIR',
                'mileage' => 110000,
                'registrationDate' => '2014-11-05'
            ],
            [
                'plate' => 'EE654EE',
                'vin' => 'VF1RFB00X53654221',
                'brand' => 'RENAULT',
                'model' => 'CAPTUR',
                'version' => '1.3 TCE',
                'energy' => 'ESSENCE',
                'horsePower' => 130,
                'fiscalPower' => 7,
                'gearBox' => 'AUTO',
                'doors' => 5,
                'seats' => 5,
                'bodyType' => 'SUV',
                'weightKg' => 1250,
                'color' => 'ORANGE',
                'mileage' => 40000,
                'registrationDate' => '2020-04-10'
            ],
            [
                'plate' => 'FF987FF',
                'vin' => 'ZFA31200003215487',
                'brand' => 'FIAT',
                'model' => '500',
                'version' => '1.2 69CH',
                'energy' => 'ESSENCE',
                'horsePower' => 69,
                'fiscalPower' => 4,
                'gearBox' => 'MANUELLE',
                'doors' => 3,
                'seats' => 4,
                'bodyType' => 'MINICITADINE',
                'weightKg' => 940,
                'color' => 'BLANC',
                'mileage' => 75000,
                'registrationDate' => '2018-01-15'
            ],
            [
                'plate' => 'GG741GG',
                'vin' => 'VF7FCKFJ9FY356487',
                'brand' => 'CITROËN',
                'model' => 'C3',
                'version' => 'BLUEHDI 75',
                'energy' => 'DIESEL',
                'horsePower' => 75,
                'fiscalPower' => 4,
                'gearBox' => 'MANUELLE',
                'doors' => 5,
                'seats' => 5,
                'bodyType' => 'CITADINE',
                'weightKg' => 1045,
                'color' => 'VERT',
                'mileage' => 50000,
                'registrationDate' => '2017-09-20'
            ],
            [
                'plate' => 'HH159HH',
                'vin' => 'JHMES16507S456987',
                'brand' => 'HONDA',
                'model' => 'CIVIC',
                'version' => '1.8 I-VTEC',
                'energy' => 'ESSENCE',
                'horsePower' => 140,
                'fiscalPower' => 7,
                'gearBox' => 'MANUELLE',
                'doors' => 5,
                'seats' => 5,
                'bodyType' => 'COMPACTE',
                'weightKg' => 1280,
                'color' => 'NOIR',
                'mileage' => 85000,
                'registrationDate' => '2013-02-11'
            ],
            [
                'plate' => 'II258II',
                'vin' => 'WAUZZZ8V2FA123654',
                'brand' => 'AUDI',
                'model' => 'A3',
                'version' => '1.4 TFSI',
                'energy' => 'ESSENCE',
                'horsePower' => 125,
                'fiscalPower' => 6,
                'gearBox' => 'AUTO',
                'doors' => 3,
                'seats' => 5,
                'bodyType' => 'COMPACTE',
                'weightKg' => 1240,
                'color' => 'GRIS',
                'mileage' => 70000,
                'registrationDate' => '2015-06-08'
            ],
            [
                'plate' => 'JJ369JJ',
                'vin' => 'YV1MW84C8J1354789',
                'brand' => 'VOLVO',
                'model' => 'V40',
                'version' => 'D2 120CH',
                'energy' => 'DIESEL',
                'horsePower' => 120,
                'fiscalPower' => 6,
                'gearBox' => 'MANUELLE',
                'doors' => 5,
                'seats' => 5,
                'bodyType' => 'COMPACTE',
                'weightKg' => 1320,
                'color' => 'BLEU MARINE',
                'mileage' => 95000,
                'registrationDate' => '2017-10-13'
            ],
        ];

        // Indexation de la flotte : ['AA123AA' => véhicule…]
        return array_column($vehicles, null, 'plate');
    }
}
