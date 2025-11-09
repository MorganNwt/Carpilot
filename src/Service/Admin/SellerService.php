<?php

namespace App\Service\Admin;

use App\Repository\UserRepository;
use App\Mapper\UserMapper;



/**
 * Service class for handling seller-related operations within the admin panel.
 *
 * This service is responsible for fetching and preparing seller data
 * for the administration interface.
 */
class SellerService
{
    /**
     * SellerService constructor.
     *
     * @param UserRepository $userRepository The repository for accessing user data.
     * @param UserMapper $UserMapper The mapper to convert User entities into response DTOs.
     */
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserMapper $UserMapper
    ) {
    }

    /**
     * Retrieves a paginated list of all users with the 'Seller' role.
     *
     * @param int $page The current page number to retrieve.
     * @param int $limit The maximum number of items to return per page.
     * @return array A structured array containing the paginated data and metadata.
     */
    public function getPaginatedSellers(int $page, int $limit): array
    {
        $paginator = $this->userRepository->findPaginatedSellers($page, $limit);
        return $this->createPaginatedResponse($paginator, $page, $limit);
    }

    /**
     * Creates a standardized paginated response array from a Paginator object.
     *
     * This private helper method transforms the paginator results into a structured
     * format suitable for an API response, including data and metadata.
     *
     * @param $paginator The Doctrine Paginator object containing the query results.
     * @param int $page The current page number.
     * @param int $limit The number of items per page.
     * @return array The structured paginated response.
     */
    public function createPaginatedResponse($paginator, int $page, int $limit)
    {
        $userDtos = array_map(
            fn($user) => $this->UserMapper->fromEntityToResponseDto($user),
            iterator_to_array($paginator)
        );

        $totalItems = count($paginator);
        $totalPages = ceil($totalItems / $limit);

        return [
            'data' => $userDtos,
            'meta' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalItems' => $totalItems,
                'limit' => $limit
            ]
        ];
    }
}

