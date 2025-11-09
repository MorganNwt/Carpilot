<?php

namespace App\Controller\Api\Public;

use OpenApi\Attributes as OA;
use App\DTO\Public\RegistrationDto;
use App\DTO\Seller\ProfileResponseDto;
use Nelmio\ApiDocBundle\Attribute\Model;
use App\Service\Public\RegistrationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[OA\Tag(name: 'Authentication')]
final class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly RegistrationService $registrationService
    ) {
    }

    #[Route('/api/sellers', name: 'api_sellers_create', methods: ['POST'])]
    #[OA\Post(
        summary: "Create a new seller account",
        description: "Registers a new user with the 'seller' role."
    )]
    #[OA\RequestBody(
        description: "Data needed to create a new seller.",
        required: true,
        content: new Model(type: RegistrationDto::class)
    )]
    #[OA\Response(
        response: 201,
        description: "Seller created successfully.",
        content: new Model(type: ProfileResponseDto::class)
    )]
    #[OA\Response(response: 409, description: "Conflict. The email address is already in use.")]
    #[OA\Response(response: 422, description: "Validation Error.")]
    public function create(#[MapRequestPayload] RegistrationDto $dto): JsonResponse
    {
        try {
            $responseDto = $this->registrationService->createSeller($dto);
            return new JsonResponse($responseDto, Response::HTTP_CREATED);
        } catch (UniqueConstraintViolationException) {
            return new JsonResponse([
                'error' => 'Data conflict',
                'message' => 'This email is already used.'
            ], Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'An unexpected error occurred',
                'message' => 'Could not create seller.',
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}