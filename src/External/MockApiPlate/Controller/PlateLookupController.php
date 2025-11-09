<?php

namespace App\External\MockApiPlate\Controller;

use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\External\MockApiPlate\Data\MockPlateDataProvider;
use App\External\MockApiPlate\Dto\PlateLookupResponseDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[OA\Tag(name: 'External mock API Plate')]
class PlateLookupController extends AbstractController
{
    public function __construct
    (
        private readonly MockPlateDataProvider $dataProvider
    ) {
    }

    #[Route(path: '/mock/plate-lookup/{plate}', name: 'mock_plate', methods: ['GET'], defaults: ['plate' => null])]
    #[OA\Get(
        '/mock/plate-lookup/{plate}',
        summary: 'Find a vehicle by plate',
        description: 'Mock API : Simulate an external API to get vehicle based on plate number.'

    )]
    #[OA\Parameter(
        name: 'plate',
        in: 'path',
        description: 'The vehicle plate to look for',
        required: true,
        example: 'AA123BB',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful vehicle lookup',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'boolean', example: false),
                new OA\Property(property: 'data', ref: new Model(type: PlateLookupResponseDto::class))
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Missing plate param',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'boolean', example: true),
                new OA\Property(property: 'data', ref: new Model(type: PlateLookupResponseDto::class))
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Vehicle not found, returns empty dto',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Plate not recognized.'),
                new OA\Property(
                    property: 'data',
                    ref: new Model(type: PlateLookupResponseDto::class)
                )
            ]
        )
    )]
    public function _invoke($plate)
    {
        if (empty($plate)) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Missing plate.'
            ], 400);
        }

        $vehicle = $this->dataProvider->findByPlate($plate);

        if ($vehicle->brand === null && $vehicle->model === null) {
            return $this->json([
                'error' => true,
                'message' => 'Plate not recognized',
                'data' => $vehicle
            ], 200);
        }

        return $this->json([
            'error' => false,
            'data' => $vehicle
        ], 200);
    }


}