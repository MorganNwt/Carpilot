<?php

namespace App\Controller\Api\Public;

use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use App\DTO\Public\EstimationRequestDto;
use App\DTO\Public\PlateLookupRequestDto;
use App\Service\Public\EstimationService;
use App\Service\Public\VehicleLookupService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Controller responsible for handling public estimation-related API requests.
 */
#[Route('/api/estimations', name: 'api_public_estimation_')]
final class EstimationController extends AbstractController
{
    public function __construct(
        private readonly VehicleLookupService $lookupService,
        private readonly EstimationService $estimationService,
    ) {}

    #[Route('/lookup-by-plate', name: 'plate_lookup', methods: ['POST'])]
    #[OA\Post(
        summary: "Lookup vehicle information by license plate",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: PlateLookupRequestDto::class))
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Returns vehicle information if found"
            )
        ]
    )]
    public function lookupByPlate(
        #[MapRequestPayload] PlateLookupRequestDto $dto
    ): JsonResponse {
        $vehicleData = $this->lookupService->lookupByPlate($dto->plate);

        // Cas : plaque inconnue ou données partielles
        if ($vehicleData === null) {
            return $this->json([
                'plate' => $dto->plate,
                'known_vehicle' => false
            ]);
        }

        // Cas : plaque connue
        return $this->json([
            'known_vehicle' => true,
            'vehicle' => $vehicleData
        ]);
    }

    #[Route('/calculate', name: 'calculate', methods: ['POST'])]
    #[OA\Post(
        summary: "Calculate estimation and return a token",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: EstimationRequestDto::class))
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Returns an estimation token"
            )
        ]
    )]
    public function calculate(
        #[MapRequestPayload] EstimationRequestDto $dto
    ): JsonResponse {
        $token = $this->estimationService->calculateAndCache($dto);

        return $this->json([
            'estimation_token' => $token
        ]);
    }
}
