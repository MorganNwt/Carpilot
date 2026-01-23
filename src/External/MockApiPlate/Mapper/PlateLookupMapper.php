<?php

namespace App\External\MockApiPlate\Mapper;

use App\External\MockApiPlate\Dto\PlateLookupResponseDto;

class PlateLookupMapper
{
    /**
     * Convertit un tableau associatif en DTO PlateLookupResponseDto.
     * Tous les champs sont optionnels (sauf la plaque), ce qui permet
     * de gérer les véhicules non trouvés proprement.
     */
    public static function fromArray(array $data): PlateLookupResponseDto
    {
        return new PlateLookupResponseDto(
            plate: $data['plate'] ?? '',
            vin: $data['vin'] ?? null,
            brand: $data['brand'] ?? null,
            model: $data['model'] ?? null,
            version: $data['version'] ?? null,
            energy: $data['energy'] ?? null,
            horsePower: $data['horsePower'] ?? null,
            fiscalPower: $data['fiscalPower'] ?? null,
            gearBox: $data['gearBox'] ?? null,
            doors: $data['doors'] ?? null,
            seats: $data['seats'] ?? null,
            bodyType: $data['bodyType'] ?? null,
            weightKg: $data['weightKg'] ?? null,
            color: $data['color'] ?? null,
            mileage: $data['mileage'] ?? null,
            registrationDate: $data['registrationDate'] ?? null
        );
    }
}
