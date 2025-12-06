<?php

namespace App\External\MockApiPlate\Controller;

use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\External\MockApiPlate\Data\MockPlateDataProvider;
use App\External\MockApiPlate\Dto\PlateLookupResponseDto;

#[OA\Tag(name: 'External mock API Plate')]
class PlateLookupController extends AbstractController
{
    public function __construct(
        private readonly MockPlateDataProvider $dataProvider
    ) {}

    #[Route('/mock/plate-lookup/{plate}', name: 'mock_plate_lookup', methods: ['GET'])]
    #[OA\Get(
        summary: 'Find a vehicle by plate',
        description: 'Simulate an external API to retrieve vehicle data from a number plate.'
    )]
    #[OA\Parameter(
        name: 'plate',
        in: 'path',
        description: 'Vehicle plate to lookup',
        example: 'AA123BB',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Response(
        response: 200,
        description: 'Vehicle found',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'boolean', example: false),
                new OA\Property(property: 'data', ref: new Model(type: PlateLookupResponseDto::class)),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Vehicle not found'
    )]
    public function __invoke(string $plate): JsonResponse
    {
        $vehicle = $this->dataProvider->findByPlate($plate);

        if ($vehicle->brand === null && $vehicle->model === null) {
            return $this->json([
                'error' => true,
                'message' => sprintf('Plate "%s" not recognized.', $plate),
                'data' => null,
            ], 404);
        }

        // ➜ véhicule trouvé
        return $this->json([
            'error' => false,
            'data' => $vehicle,
        ], 200);
    }
}
