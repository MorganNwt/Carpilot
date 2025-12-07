<?php

namespace App\Controller\Api\Public;


use Psr\Log\LoggerInterface;
use OpenApi\Attributes as OA;
use App\DTO\Public\EstimationRequestDto;
use App\DTO\Public\PlateLookupRequestDto;
use App\Service\Public\EstimationService;
use App\Service\Public\VehicleLookupService;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Controller responsible for handling estimation-related API requests.
 *
 * This class provides endpoints for public estimation operations.
 */
#[Route('/api/estimations', 'api_public_estimation_')]
final class EstimationController extends AbstractController
{
    public function __construct(
        private readonly VehicleLookupService $lookupService,
        private readonly EstimationService $estimationService,
        private readonly LoggerInterface $logger
    ) {}


    #[Route('/lookup-by-plate', 'plate_lookup', methods: ['POST'])]
    /*
    #[OA\Post(
        summary: "Lookup vehicle by license plate",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: PlateLookupRequestDto::class))
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Vehicle data found",
                content: new OA\JsonContent(
                    // ici tu peux détailler les propriétés
                )
            ),
            new OA\Response(
                response: 503,
                description: "Service unavailable"
            )
        ]
    )]
        */
    public function lookupByPlate(#[MapRequestPayload] PlateLookupRequestDto $dto)
    {
        try {
            $vehicleData = $this->lookupService->lookupByPlate($dto->plate);
            return new JsonResponse($vehicleData);
        } catch (\Exception $e) {

            // ✅ Le logger fonctionne MAINTENANT
            $this->logger->error("[LOOKUP ERROR] " . $e->getMessage());

            return new JsonResponse(['error' => 'Lookup failed'], 503);
        }
    }


    #[Route('/calculate', 'calculate', methods: ['POST'])]
    /*
    #[OA\Post(
        summary: "Calculate estimation and get token.",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: EstimationRequestDto::class))
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Returns an estimation token",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "estimation_token", type: "string", example: "abc123"),
                    ]
                )
            )
        ]
    )]*/
    public function calculate(#[MapRequestPayload] EstimationRequestDto $dto)
    {

        $token = $this->estimationService->calculateAndCache($dto);

        return new JsonResponse([
            'estimation_token' => $token
        ]);
    }
}
