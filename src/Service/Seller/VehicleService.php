<?php

namespace App\Service\Seller;

use App\Entity\Vehicle;
use App\Entity\Estimation;
use App\Entity\User\Seller;
use App\Mapper\VehicleMapper;
use App\DTO\Vehicle\UpdateVehicleDto;
use App\Repository\VehicleRepository;
use App\DTO\Vehicle\VehicleResponseDto;
use App\DTO\Public\EstimationRequestDto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Class VehicleService
 * Contains all business logic to interact with Vehicle entities.
 */
class VehicleService
{
    /**
     * @param EntityManagerInterface $em The entity manager for database writes (persist, flush, remove).
     * @param VehicleRepository $repository The repository for database reads (find, findBy).
     * @param VehicleMapper $mapper The mapper to translate between DTOs and Entities.
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly VehicleRepository $repository,
        private readonly VehicleMapper $mapper
    ) {}


    /**
     * Creates a Vehicle entity from cached estimation data using the provided token and Seller.
     *
     * Retrieves estimation data from cache, maps it to a DTO, creates a Vehicle entity,
     * associates it with the Seller and Estimation, persists it, and removes the cache item.
     *
     * @param string $token The cache token for the estimation data.
     * @param Seller $seller The seller entity to associate with the vehicle.
     * @return Vehicle The newly created Vehicle entity.
     * @throws NotFoundHttpException If the estimation token is expired or not found in cache.
     */
    public function createVehicleFromEstimation(string $token, Seller $seller): Vehicle
    {
        $cache = new FilesystemAdapter();
        $item = $cache->getItem($token);

        if (!$item->isHit()) {
            throw new NotFoundHttpException('Estimation token expired.');
        }

        $data = $item->get();
        $vehicleData = $data['vehicle_data'] ?? null;

        if (!$vehicleData) {
            throw new NotFoundHttpException('Estimation data missing.');
        }

        $dto = new EstimationRequestDto();
        foreach ($vehicleData as $key => $value) {
            if (property_exists($dto, $key)) {
                $dto->$key = $value;
            }
        }

        // Chercher véhicule existant (vin puis plate)
        $existing = null;

        if (!empty($dto->vin)) {
            $existing = $this->repository->findOneBy(['vin' => $dto->vin]);
        }

        if (!$existing && !empty($dto->plate)) {
            $existing = $this->repository->findOneBy(['plate' => $dto->plate]);
        }

        if ($existing) {
            if ($existing->getSeller()?->getId() !== $seller->getId()) {
                throw new ConflictHttpException("Ce véhicule est déjà associé à un autre vendeur.");
            }
            $vehicle = $existing;
        } else {
            $vehicle = $this->mapper->fromCreateDtoToEntity($dto);
            $vehicle->setSeller($seller);
        }

        // Un dossier existant conserve son agence ; un nouveau dépend de celle du vendeur.
        $agency = $vehicle->getAgency() ?? $seller->getAgency();
        if ($agency === null) {
            throw new ConflictHttpException('Votre compte doit être rattaché à une agence avant de pouvoir enregistrer un véhicule.');
        }
        $vehicle->setAgency($agency);

        $estimation = $vehicle->getEstimation() ?? new Estimation();
        if ($estimation->getAgency() === null) {
            $estimation->setAgency($agency);
        }
        $estimation->setEstimatedPrice($data['price']);

        $vehicle->setEstimation($estimation);

        if (!$existing) {
            $this->em->persist($vehicle);
        }

        $this->em->flush();

        $cache->deleteItem($token);

        return $vehicle;
    }




    /**
     * Finds all vehicles belonging to a specific seller, ordered by creation date.
     *
     * @param Seller $seller The seller whose vehicles to find.
     * @return VehicleResponseDto[] An array of vehicle response DTOs.
     */
    public function findVehiclesBySeller(Seller $seller): array
    {
        $vehicles = $this->repository->findBy(['seller' => $seller], ['createdAt' => 'DESC']);

        $responseDtos = [];

        foreach ($vehicles as $vehicle) {
            $responseDtos[] = $this->mapper->fromEntityToResponseDto($vehicle);
        }

        return $responseDtos;
    }

    /**
     * Finds a single vehicle by its entity and returns its DTO representation.
     *
     * @param Vehicle $vehicle The vehicle entity.
     * @return VehicleResponseDto The DTO representation of the vehicle.
     */
    public function findVehiclebyId(Vehicle $vehicle): VehicleResponseDto
    {
        return $this->mapper->fromEntityToResponseDto($vehicle);
    }

    /**
     * Updates a vehicle's data from a DTO.
     *
     * @param Vehicle $vehicle The vehicle entity to update.
     * @param UpdateVehicleDto $dto The DTO containing the new data.
     * @return VehicleResponseDto The updated vehicle data as a DTO.
     */
    public function updateVehicle(Vehicle $vehicle, UpdateVehicleDto $dto): VehicleResponseDto
    {
        $this->mapper->fromUpdateDtoToEntity($vehicle, $dto);

        $this->em->flush();

        return $this->mapper->fromEntityToResponseDto($vehicle);
    }

    /**
     * Deletes a vehicle from the database.
     *
     * @param Vehicle $vehicle The vehicle entity to delete.
     */
    public function deleteVehicle(Vehicle $vehicle): void
    {
        $this->em->remove($vehicle);
        $this->em->flush();
    }
}
