<?php

namespace App\Service\Agent;

use App\Enum\EstimationStatus;
use App\Mapper\VehicleMapper;
use App\Repository\VehicleRepository;

final class AgentEstimationService
{
    public function __construct(
        private readonly VehicleRepository $vehicleRepository,
        private readonly VehicleMapper $vehicleMapper,
    ) {}

    public function listByStatus(?EstimationStatus $status): array
    {
        $qb = $this->vehicleRepository->createQueryBuilder('v')
            ->innerJoin('v.estimation', 'e')
            ->addSelect('e')
            ->leftJoin('v.seller', 's')
            ->addSelect('s');

        if ($status !== null) {
            $qb->andWhere('e.status = :status')
                ->setParameter('status', $status);
        }

        $vehicles = $qb->orderBy('e.createdAt', 'DESC')->getQuery()->getResult();

        return array_map(fn($v) => $this->vehicleMapper->fromEntityToResponseDto($v), $vehicles);
    }
}
