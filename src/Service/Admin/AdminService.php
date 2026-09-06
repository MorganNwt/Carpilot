<?php

namespace App\Service\Admin;

use App\Repository\UserRepository;
use App\Mapper\UserMapper;

/**
 * Service class for handling admin-related operations within the admin panel.
 *
 * This service is responsible for fetching and preparing admin data
 * for the administration interface.
 */
final class AdminService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserMapper $userMapper
    ) {}

    /**
     * Retrieves a paginated list of all users with the 'Admin' role.
     */
    public function getPaginatedAdmins(int $page, int $limit): array
    {
        $paginator = $this->userRepository->findPaginatedAdmins($page, $limit);

        return $this->createPaginatedResponse($paginator, $page, $limit);
    }

    /**
     * Creates a standardized paginated response array from a Paginator object.
     *
     * @param mixed $paginator Doctrine paginator
     */
    private function createPaginatedResponse($paginator, int $page, int $limit): array
    {
        $userDtos = array_map(
            fn($user) => $this->userMapper->fromEntityToResponseDto($user),
            iterator_to_array($paginator)
        );

        $totalItems = count($paginator);
        $totalPages = (int) ceil($totalItems / $limit);

        return [
            'data' => $userDtos,
            'meta' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalItems' => $totalItems,
                'limit' => $limit,
            ],
        ];
    }
}
