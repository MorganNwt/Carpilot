<?php

namespace App\DTO\Public;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(
    title: "Request Password Reset DTO",
    description: "Data structure for requesting a password reset email. The user provides their email to receive a reset link."
)]
class ResetPasswordRequestDto
{
    #[OA\Property(
        description: "The email address of the account for which to reset the password.",
        type: 'string',
        format: 'email',
        example: 'seller@example.com'
    )]
    #[Assert\NotBlank(message: 'You must provide an email.')]
    #[Assert\Email(message: 'You must provide a valid email address.')]
    public ?string $email = null;
}
