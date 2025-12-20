<?php

namespace App\Mapper;

use App\DTO\Public\EstimationRequestDto;
use App\DTO\Vehicle\CreateVehicleDto;
use App\DTO\Vehicle\UpdateVehicleDto;
use App\DTO\Vehicle\VehicleResponseDto;
use App\Entity\Vehicle;
use DateTimeImmutable;

/**
 * Class VehicleMapper
 * This class is responsible for converting Vehicle DTOs to Entities and vice-versa.
 * It acts as a translation layer between the API data structure and the database entity.
 */
class VehicleMapper
{
    public function __construct(
        private readonly EstimationMapper $estimationMapper
    ){

    }
    /**
     * Transforms a CreateVehicleDto into a new Vehicle entity.
     *
     * @param EstimationRequestDto $dto The data transfer object from the API request.
     * @return Vehicle The newly created Vehicle entity, ready to be persisted.
     */
    public function fromCreateDtoToEntity(EstimationRequestDto $dto): Vehicle
    {
        $vehicle = new Vehicle();

        $vehicle->setPlate($dto->plate);
        $vehicle->setVin($dto->vin);
        $vehicle->setBrand($dto->brand);
        $vehicle->setModel($dto->model);
        $vehicle->setVersion($dto->version);
        $vehicle->setEnergy($dto->energy);
        $vehicle->setHorsePower($dto->horsePower);
        $vehicle->setFiscalPower($dto->fiscalPower);
        $vehicle->setGearBox($dto->gearBox);
        $vehicle->setDoors($dto->doors);
        $vehicle->setSeats($dto->seats);
        $vehicle->setBodyType($dto->bodyType);
        $vehicle->setWeightKg($dto->weightKg);
        $vehicle->setColor($dto->color);
        $vehicle->setRegistrationDate(new DateTimeImmutable($dto->registrationDate));

        return $vehicle;
    }

    /**
     * Applies changes from an UpdateVehicleDto to an existing Vehicle entity.
     * This method only updates fields that are not null in the DTO
     * for partial updates of a vehicle's data.
     *
     * @param Vehicle $vehicle The existing Vehicle entity loaded from the database.
     * @param UpdateVehicleDto $dto The DTO containing the data to be updated. 
     */
    public function fromUpdateDtoToEntity(Vehicle $vehicle, UpdateVehicleDto $dto): Vehicle
    {
        if ($dto->plate !== null) {
            $vehicle->setPlate($dto->plate);
        }
        if ($dto->vin !== null) {
            $vehicle->setVin($dto->vin);
        }
        if ($dto->brand !== null) {
            $vehicle->setBrand($dto->brand);
        }
        if ($dto->model !== null) {
            $vehicle->setModel($dto->model);
        }
        if ($dto->version !== null) {
            $vehicle->setVersion($dto->version);
        }
        if ($dto->energy !== null) {
            $vehicle->setEnergy($dto->energy);
        }
        if ($dto->horsePower !== null) {
            $vehicle->setHorsePower($dto->horsePower);
        }
        if ($dto->fiscalPower !== null) {
            $vehicle->setFiscalPower($dto->fiscalPower);
        }
        if ($dto->gearBox !== null) {
            $vehicle->setGearBox($dto->gearBox);
        }
        if ($dto->doors !== null) {
            $vehicle->setDoors($dto->doors);
        }
        if ($dto->seats !== null) {
            $vehicle->setSeats($dto->seats);
        }
        if ($dto->bodyType !== null) {
            $vehicle->setBodyType($dto->bodyType);
        }
        if ($dto->weightKg !== null) {
            $vehicle->setWeightKg($dto->weightKg);
        }
        if ($dto->color !== null) {
            $vehicle->setColor($dto->color);
        }
        if ($dto->registrationDate !== null) {
            $vehicle->setRegistrationDate(new DateTimeImmutable($dto->registrationDate));
        }

        return $vehicle;
    }

    /**
     * Transforms a Vehicle entity into a VehicleResponseDto for API responses.
     *
     * @param Vehicle $vehicle The entity coming from the database.
     * @return VehicleResponseDto The response DTO with safe and formatted data.
     */
    public function fromEntityToResponseDto(Vehicle $vehicle)
    {
        $EstimationDto = $this->estimationMapper->fromEntityToResponseDto($vehicle->getEstimation());
        

        return new VehicleResponseDto(
            $vehicle->getId(),
            $vehicle->getPlate(),
            $vehicle->getVin(),
            $vehicle->getBrand(),
            $vehicle->getModel(),
            $vehicle->getVersion(),
            $vehicle->getEnergy(),
            $vehicle->getHorsePower(),
            $vehicle->getSeller()?->getId(),
            $vehicle->getFiscalPower(),
            $vehicle->getGearBox(),
            $vehicle->getDoors(),
            $vehicle->getSeats(),
            $vehicle->getBodyType(),
            $vehicle->getWeightKg(),
            $vehicle->getColor(),
            $vehicle->getRegistrationDate(),
            $vehicle->getCreatedAt(),
            $vehicle->getUpdatedAt(),
            $EstimationDto
        );
    }

    public function fromApiToCreateVehicleDto(array $data)
    {
        $dto = new CreateVehicleDto();

        $dto->plate = $data['plate'] ?? null;
        $dto->vin = $data['vin'] ?? null;
        $dto->brand = $data['brand'] ?? null;
        $dto->model = $data['model'] ?? null;
        $dto->version = $data['version'] ?? null;
        $dto->energy = $data['energy'] ?? null;
        $dto->horsePower = $data['hhorsePowerorse'] ?? null;
        $dto->fiscalPower = $data['fiscalPower'] ?? null;
        $dto->gearBox = $data['gearBox'] ?? null;
        $dto->doors = $data['doors'] ?? null;
        $dto->seats = $data['seats'] ?? null;
        $dto->bodyType = $data['bodyType'] ?? null;
        $dto->weightKg = $data['weightKg'] ?? null;
        $dto->color = $data['color'] ?? null;
        $dto->registrationDate = $data['registrationDate'] ?? null;

        return $dto;
    }
    
}
