<?php

namespace App\DTO\Public;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(
    title: "Create Seller DTO",
    description: "Data structure required to create a new seller account."
)]
class RegistrationDto
{
    #[OA\Property(description: "Seller's first name.", example: "John")]
    #[Assert\NotBlank(message: "The first name cannot be blank.")]
    public ?string $firstName = null;

    #[OA\Property(description: "Seller's last name.", example: "Doe")]
    #[Assert\NotBlank(message: "The last name cannot be blank.")]
    public ?string $lastName = null;

    #[OA\Property(description: "Seller's unique email address.", example: "john.doe@example.com")]
    #[Assert\NotBlank(message: "The email cannot be blank.")]
    #[Assert\Email(message: "The email '{{ value }}' is not a valid email.")]
    public ?string $email = null;

    #[OA\Property(description: "Seller's password. Must be at least 8 characters.", example: "S3cr3tP@ssword!")]
    #[Assert\NotBlank(message: "The password cannot be blank.")]
    #[Assert\Length(min: 8, minMessage: "Your password must be at least {{ limit }} characters long.")]
    public ?string $password = null;

    #[OA\Property(description: "Seller's phone number.", example: "+33612345678")]
    #[Assert\NotBlank(message: "The phone number cannot be blank.")]
    public ?string $phone = null;

    #[OA\Property(description: "Seller's street address.", example: "123 Rue de la République")]
    #[Assert\NotBlank(message: "The address cannot be blank.")]
    public ?string $address = null;

    #[OA\Property(description: "Seller's city.", example: "Lyon")]
    #[Assert\NotBlank(message: "The city cannot be blank.")]
    public ?string $city = null;

    #[OA\Property(description: "Seller's postal code.", example: "69001")]
    #[Assert\NotBlank(message: "The postal code cannot be blank.")]
    public ?string $postalCode = null;

    #[OA\Property(description: "Seller's country.", example: "France")]
    #[Assert\NotBlank(message: "The country cannot be blank.")]
    public ?string $country = null;

    #[OA\Property(description: "User consent to privacy policy (RGPD).", example: true)]
    #[Assert\NotNull(message: "RGPD consent is required.")]
    #[Assert\IsTrue(message: "You must accept the privacy policy.")]
    public ?bool $rgpdConsent = null;

    #[OA\Property(description: "Selected agency ID.", example: 3)]
    #[Assert\NotNull(message: "Agency is required.")]
    #[Assert\Positive(message: "Agency ID must be positive.")]
    public ?int $agencyId = null;
}
