<?php

namespace App\Service\Admin;

use App\Repository\VehicleRepository;
use App\Mapper\VehicleMapper;
use App\DTO\Vehicle\VehicleResponseDto;
use App\Entity\Vehicle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

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
        private readonly VehicleMapper $vehicleMapper,
        private readonly EntityManagerInterface $em,
    ) {}

    public function deleteVehicle(Vehicle $vehicle): void
    {
        $estimation = $vehicle->getEstimation();
        if ($estimation !== null) {
            if ($estimation->getTransaction() !== null || !$estimation->getAppointments()->isEmpty()) {
                throw new ConflictHttpException('Ce véhicule est lié à une transaction ou à un rendez-vous et ne peut pas être supprimé.');
            }

            foreach ($estimation->getNotifications() as $notification) {
                $estimation->removeNotification($notification);
            }
        }

        $this->em->remove($vehicle);
        $this->em->flush();
    }

    public function getPaginatedVehicles(int $page, int $limit = 10): array
    {
        $page = max(1, $page);
        $limit = max(1, min(100, $limit));
        $totalItems = $this->vehicleRepository->count([]);
        $vehicles = $this->vehicleRepository->findBy(
            [], ['createdAt' => 'DESC', 'id' => 'DESC'], $limit, ($page - 1) * $limit
        );

        return [
            'data' => array_map($this->vehicleMapper->fromEntityToResponseDto(...), $vehicles),
            'meta' => [
                'currentPage' => $page,
                'totalPages' => (int) ceil($totalItems / $limit),
                'totalItems' => $totalItems,
                'limit' => $limit,
            ],
        ];
    }
}
