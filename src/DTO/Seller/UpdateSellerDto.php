<?php

namespace App\DTO\Seller;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(
    title: "Update Seller DTO",
    description: "Data structure for updating an existing seller account. All fields are optional."
)]
class UpdateSellerDto
{
    #[OA\Property(description: "The seller's first name.", type: 'string', example: 'John')]
    #[Assert\Length(min: 2, max: 50)]
    public ?string $firstName = null;

    #[OA\Property(description: "The seller's last name.", type: 'string', example: 'Doe')]
    #[Assert\Length(min: 2, max: 50)]
    public ?string $lastName = null;

    #[OA\Property(description: "The seller's unique email address.", type: 'string', format: 'email', example: 'john.doe@example.com')]
    #[Assert\Email]
    public ?string $email = null;

    #[OA\Property(description: "The seller's street address.", type: 'string', example: '123 Main Street')]
    #[Assert\Length(min: 5, max: 255)]
    public ?string $address = null;

    #[OA\Property(description: "The city.", type: 'string', example: 'Anytown')]
    #[Assert\Length(min: 2, max: 100)]
    public ?string $city = null;

    #[OA\Property(description: "The postal code.", type: 'string', example: '12345')]
    #[Assert\Length(min: 3, max: 10)]
    public ?string $postalCode = null;

    #[OA\Property(description: "The country.", type: 'string', example: 'France')]
    #[Assert\Length(min: 2, max: 100)]
    public ?string $country = null;

    #[OA\Property(description: "The seller's phone number.", type: 'string', example: '+33612345678')]
    #[Assert\Length(min: 10, max: 20)]
    public ?string $phone = null;
}
