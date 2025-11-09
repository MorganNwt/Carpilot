<?php

namespace App\DTO\Seller;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[OA\Schema(
    title: "Change Password",
    description: "Data structure for changing the current user's password."
)]
class ChangePasswordDto
{
    #[OA\Property(description: "The user's current password for verification.", type: 'string', format: 'password', example: 'MyOldPassword123')]
    #[Assert\NotBlank(message: "This field cannot be blank.")]
    public ?string $currentPassword = null;

    #[OA\Property(description: "The desired new password.", type: 'string', format: 'password', example: 'MyNewSecureP@ssw0rd!')]
    #[Assert\NotBlank(message: "This field cannot be blank.")]
    #[Assert\Length(
        min: 8,
        minMessage: "Your new password must be at least {{ limit }} characters long."
    )]
    #[Assert\NotCompromisedPassword(message: "This password has been exposed in a data breach. Please choose a different one.")]
    public ?string $newPassword = null;

    #[OA\Property(description: "Confirmation of the new password.", type: 'string', format: 'password', example: 'MyNewSecureP@ssw0rd!')]
    #[Assert\EqualTo(
        propertyPath: 'newPassword',
        message: 'New password and password confirmation should match.'
    )]
    #[Assert\NotBlank(message: "This field cannot be blank.")]
    public ?string $confirmPassword = null;

    /**
     * Custom validation to ensure the new password is not the same as the current one.
     */
    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context, $payload): void
    {
        if (null !== $this->newPassword && $this->newPassword === $this->currentPassword) {
            $context->buildViolation('The new password cannot be the same as the current password.')
                ->atPath('newPassword')
                ->addViolation();
        }
    }
}
