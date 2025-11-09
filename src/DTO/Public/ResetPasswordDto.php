<?php

namespace App\DTO\Public;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(
    title: "Reset Password DTO",
    description: "Data structure for resetting a user's password using a token."
)]
class ResetPasswordDto
{
    #[OA\Property(description: "The password reset token sent to the user.", type: 'string', example: 'c9a8e7f6d5b4c3a2b1a0f9e8d7c6b5a4')]
    #[Assert\NotBlank(message: 'The token cannot be blank.')]
    public ?string $token = null;

    #[OA\Property(description: "The new password for the account.", type: 'string', format: 'password', example: 'MyNewSecureP@ssw0rd!')]
    #[Assert\NotBlank(message: 'The new password cannot be blank.')]
    #[Assert\Length(
        min: 8,
        minMessage: 'Your password must be at least {{ limit }} characters long.'
    )]
    #[Assert\NotCompromisedPassword(message: 'This password has been exposed in a data breach. Please choose a different one.')]
    public ?string $newPassword = null;

    #[OA\Property(description: "Confirmation of the new password.", type: 'string', format: 'password', example: 'MyNewSecureP@ssw0rd!')]
    #[Assert\NotBlank(message: 'The password confirmation cannot be blank.')]
    #[Assert\EqualTo(
        propertyPath: 'newPassword',
        message: 'The password and its confirmation do not match.'
    )]
    public ?string $confirmPassword = null;
}
