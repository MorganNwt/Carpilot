<?php

namespace App\External\MockApiPlate\Mapper;

use App\External\MockApiPlate\Dto\PlateLookupResponseDto;



class PlateLookupMapper
{

    public static function fromArray(array $data): PlateLookupResponseDto
    {
        return new PlateLookupResponseDto(
            plate: $data['plate'],
            vin: $data['vin'] ?? null,
            brand: $data['brand'] ?? null,
            model: $data['model'] ?? null,
            version: $data['version'] ?? null,
            energy: $data['energy'] ?? null,
            fiscalPower: $data['fiscalPower'] ?? null,
            gearBox: $data['gearBox'] ?? null,
            doors: $data['doors'] ?? null,
            seats: $data['seats'] ?? null,
            bodyType: $data['bodyType'] ?? null,
            weightKg: $data['weightKg'] ?? null,
            color: $data['color'] ?? null,
            registrationDate: $data['registrationDate'] ?? null
        );
    }

}