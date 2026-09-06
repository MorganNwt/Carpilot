<?php

namespace App\Service\Public;

use App\DTO\Public\ResetPasswordDto;
use App\DTO\Public\ResetPasswordRequestDto;
use App\Entity\User\Seller;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfonycasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use Symfonycasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

/**
 * Handles the business logic for the password reset process.
 */
class PasswordService
{
    /**
     * @param ResetPasswordHelperInterface $resetPasswordHelper Service from the bundle to generate and validate tokens.
     * @param UserRepository $userRepository Repository to find users.
     * @param EntityManagerInterface $em Entity Manager to save changes to the database.
     * @param MailerInterface $mailer Service to send emails.
     * @param UserPasswordHasherInterface $passwordHasher Service to hash passwords.
     * @param LoggerInterface $logger Service to record logs.
     */
    public function __construct(
        private readonly ResetPasswordHelperInterface $resetPasswordHelper,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $em,
        private readonly MailerInterface $mailer,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * Handles a password reset request.
     * Finds the user by email, generates a reset token, and sends the reset email.
     * The method returns silently if the user is not found or if a request is already in progress
     * for security reasons (to prevent user enumeration).
     *
     * @param ResetPasswordRequestDto $dto The DTO containing the user's email.
     * @throws TransportExceptionInterface If sending the email fails.
     */
    public function handlePasswordRequest(ResetPasswordRequestDto $dto): void
    {
        $user = $this->userRepository->findOneBy(['email' => $dto->email]);

        if (!$user) {
            $this->logger->warning('User with email ' . $dto->email . ' not found.');
            return;
        }

        if (!$user instanceof Seller) {
            $this->logger->warning('User with id ' . $user->getId() . ' is not a seller.');
            return;
        }

        $this->logger->info('User with id ' . $user->getId() . ' found. Generating token...');

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->logger->warning('Throttling password reset for user ID ' . $user->getId() . '. Reason: ' . $e->getReason());
            return;
        }

        $resetUrl = "http://localhost:8000/reset-password?token=" . $resetToken->getToken();

        $email = (new TemplatedEmail())
            ->from('noreply@carpilot.fr')
            ->to($user->getEmail())
            ->subject('Password reset')
            ->htmlTemplate('emails/password_reset.html.twig')
            ->context([
                'resetUrl' => $resetUrl,
                'tokenLifetime' => $this->resetPasswordHelper->getTokenLifetime() / 3600
            ]);

        $this->logger->info('Sending reset password email to User : ' . $user->getId());

        try {
            $this->mailer->send($email);
            $this->logger->info('Email successfully sent to ' . $user->getEmail());
        } catch (TransportExceptionInterface $e) {
            $this->logger->warning('Failed to send reset password email to :' . $user->getEmail());
            throw $e;
        }
    }

    /**
     * Handles the actual password reset.
     * Validates the token, updates the user's password, and removes the token.
     *
     * @param ResetPasswordDto $dto The DTO containing the token and the new password.
     * @throws NotFoundHttpException If the token is invalid or has expired.
     */
    public function handlePasswordReset(ResetPasswordDto $dto): void
    {
        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($dto->token);
        } catch (ResetPasswordExceptionInterface $e) {
            //transforms the generic bundle exception into a clearer HTTP exception.
            throw new NotFoundHttpException('The reset password token is invalid or has expired. Reason: ' . $e->getReason());
        }

        // The token is valid, removed so it cannot be reused.
        $this->resetPasswordHelper->removeResetRequest($dto->token);

        // hashes and set the new password.
        $hashedPassword = $this->passwordHasher->hashPassword($user, $dto->newPassword);
        $user->setPassword($hashedPassword);

        // saves the changes to the database.
        $this->em->flush();
    }
}
