<?php

namespace App\Service\Admin;

use App\Repository\UserRepository;
use App\Repository\VehicleRepository;

final class AdminDashboardService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly VehicleRepository $vehicleRepository,
    ) {}

    public function getDashboard(): array
    {
        return [
            'counts' => [
                'users' => $this->userRepository->countAllUsers(),
                'agents' => $this->userRepository->countAgents(),
                'sellers' => $this->userRepository->countSellers(),
                'vehicles' => $this->vehicleRepository->count([]),
            ],
        ];
    }
}
