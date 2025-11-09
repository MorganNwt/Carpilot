<?php

namespace App\Controller\Api\Public;

use App\DTO\Public\ResetPasswordDto;
use App\DTO\Public\ResetPasswordRequestDto;
use App\Service\Public\PasswordService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[OA\Tag(name: 'Authentication')]
#[Route('/api/reset-password', name: 'api_password_')]
class ResetPasswordController extends AbstractController
{
    public function __construct(
        private readonly PasswordService $passwordService,
        private readonly LoggerInterface $logger
    ) {
    }


    #[Route('/request', name: 'request_reset', methods: ['POST'])]
    #[OA\Post(
        summary: "Request a password reset",
        description: "Initiates the password reset process for a user by sending them an email with a reset link."
    )]
    #[OA\RequestBody(
        description: "The user's email address.",
        required: true,
        content: new Model(type: ResetPasswordRequestDto::class)
    )]
    #[OA\Response(
        response: 204,
        description: "Request received. If an account with this email exists, a reset link has been sent. This response is always returned to prevent user enumeration."
    )]
    #[OA\Response(
        response: 422,
        description: "Validation Error. The data provided is invalid (e.g., malformed email)."
    )]
    public function request(#[MapRequestPayload] ResetPasswordRequestDto $dto): JsonResponse
    {
        try {
            $this->passwordService->handlePasswordRequest($dto);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Password reset email could not be sent : ' . $e->getMessage());
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }


    #[Route('/reset', name: 'reset', methods: ['POST'])]
    #[OA\Post(
        summary: "Reset the password",
        description: "Sets a new password using a valid reset token."
    )]
    #[OA\RequestBody(
        description: "A valid reset token and the new password.",
        required: true,
        content: new Model(type: ResetPasswordDto::class)
    )]
    #[OA\Response(
        response: 200,
        description: "Password has been successfully reset.",
        content: new OA\JsonContent(
            properties: [new OA\Property(property: 'message', type: 'string', example: 'Password successfully reset.')]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Not Found. The token is invalid or has expired."
    )]
    #[OA\Response(
        response: 422,
        description: "Validation Error. The data provided is invalid (e.g., password mismatch)."
    )]
    public function reset(#[MapRequestPayload] ResetPasswordDto $dto): JsonResponse
    {
        try {
            $this->passwordService->handlePasswordReset($dto);
            return new JsonResponse(['message' => 'Password successfully reset.']);
        } catch (NotFoundHttpException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }
}
