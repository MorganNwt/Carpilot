<?php

namespace App\DTO\Admin;

use OpenApi\Attributes as OA;

#[OA\Schema(
    title: "Admin - User Response",
    description: "Generic data structure for representing any user in the admin panel."
)]
class UserResponseDto
{
    /**
     * @param int $id
     * @param string $userType ('seller', 'agent').
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string $phone
     * @param array<string> $roles
     * @param string|null $employeeId The employee ID only for Agents.
     * @param string|null $fullAddress The full address only for Sellers.
     * @param \DateTimeImmutable $createdAt
     */
    public function __construct(
        #[OA\Property(example: 1)]
        public readonly int $id,

        #[OA\Property(example: "seller")]
        public readonly string $userType,

        #[OA\Property(example: "Jean")]
        public readonly string $firstName,

        #[OA\Property(example: "Dupont")]
        public readonly string $lastName,

        #[OA\Property(example: "jean.dupont@example.com")]
        public readonly string $email,

        #[OA\Property(example: "0612345678")]
        public readonly string $phone,

        #[OA\Property(type: "array", items: new OA\Items(type: "string"), example: ["ROLE_SELLER", "ROLE_USER"])]
        public readonly array $roles,

        #[OA\Property(description: "Only for Agent/Admin", nullable: true, example: "EMP123")]
        public readonly ?string $employeeId,

        #[OA\Property(description: "Only for Seller", nullable: true, example: "12 rue de la Paix, 75001 Paris, France")]
        public readonly ?string $fullAddress,

        #[OA\Property]
        public readonly \DateTimeImmutable $createdAt,

        #[OA\Property(description: "List of vehicles, if the user is a Seller.")]
        public readonly array $vehicles = []
    ) {
    }
}
