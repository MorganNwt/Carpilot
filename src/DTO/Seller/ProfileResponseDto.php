<?php

namespace App\DTO\Seller;

use DateTimeImmutable;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: "Seller Profile",
    description: "Information about a seller's profile, returned by the API."
)]
class ProfileResponseDto
{
    public function __construct(
        #[OA\Property(description: "The unique identifier of the seller.", example: 1)]
        public readonly ?int $id,

        #[OA\Property(description: "Seller's first name.", example: "Jane")]
        public readonly ?string $firstName,

        #[OA\Property(description: "Seller's last name.", example: "Doe")]
        public readonly ?string $lastName,

        #[OA\Property(description: "Seller's email address.", example: "jane.doe@example.com")]
        public readonly ?string $email,

        #[OA\Property(description: "Seller's phone number.", example: "+33612345678")]
        public readonly ?string $phone,

        #[OA\Property(description: "Seller's street address.", example: "456 Avenue des Ventes", nullable: true)]
        public readonly ?string $address,

        #[OA\Property(description: "Seller's city.", example: "Paris", nullable: true)]
        public readonly ?string $city,

        #[OA\Property(description: "Seller's postal code.", example: "75001", nullable: true)]
        public readonly ?string $postalCode,

        #[OA\Property(description: "Seller's country.", example: "France", nullable: true)]
        public readonly ?string $country,


        #[OA\Property(description: "The date and time the seller account was created.", type: "string", format: "date-time", example: "2025-06-25T20:15:47+00:00")]
        public readonly ?DateTimeImmutable $createdAt,

        public readonly ?array $vehicles = []
    ) {
    }
}
