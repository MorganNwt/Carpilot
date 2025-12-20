<?php

namespace App\Service\Admin;

use App\Repository\VehicleRepository;
use App\Mapper\VehicleMapper;
use App\DTO\Vehicle\VehicleResponseDto;

/**
 * Service dédié aux opérations ADMIN sur les véhicules.
 *
 * Responsabilité :
 *  - récupérer tous les véhicules
 *  - retourner des DTO prêts à être sérialisés
 *
 * Le choix des groupes de sérialisation est laissé au Controller.
 */
class AdminVehicleService
{
    public function __construct(
        private readonly VehicleRepository $vehicleRepository,
        private readonly VehicleMapper $vehicleMapper
    ) {}

    /**
     * ADMIN – Retrieve all vehicles in the system.
     *
     * @return VehicleResponseDto[] An array of vehicle response DTOs.
     */
    public function findAllVehicles(): array
    {
        $vehicles = $this->vehicleRepository->findAll();

        $responseDtos = [];

        foreach ($vehicles as $vehicle) {
            $responseDtos[] = $this->vehicleMapper->fromEntityToResponseDto($vehicle);
        }

        return $responseDtos;
    }
}
