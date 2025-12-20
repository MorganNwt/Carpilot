<?php

namespace App\Controller\Api\Admin;

use App\Entity\Vehicle;
use App\Mapper\VehicleMapper;

use OpenApi\Attributes as OA;
use App\DTO\Vehicle\UpdateVehicleDto;
use App\Service\Seller\VehicleService;
use App\Service\Admin\AdminVehicleService;
use App\DTO\Vehicle\VehicleResponseDto;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Handles CRUD operations for ADMIN on all vehicles.
 */
#[Route('/api/admin/vehicles/', name: 'api_admin_vehicle_')]
#[OA\Tag(name: 'Admin Vehicles')]
#[Security(name: 'bearerAuth')]
#[IsGranted('ROLE_ADMIN')]
final class VehicleController extends AbstractController
{
    public function __construct(
        private readonly VehicleService $vehicleService,
        private readonly VehicleMapper $vehicleMapper,
        private readonly AdminVehicleService $adminVehicleService
    ) {}

    /**
     * List ALL vehicles
     */
    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(
        summary: "List all vehicles (admin)",
        description: "Retrieves the list of all vehicles in the system."
    )]
    #[OA\Response(
        response: 200,
        description: "Returns the list of vehicles.",
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: VehicleResponseDto::class))
        )
    )]
    public function listAll()
    {
        return $this->json(
            $this->adminVehicleService->findAllVehicles()
        );
    }

    /**
     *  Show one vehicle
     */
    #[Route('{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[OA\Get(
        summary: "Get a vehicle details (admin)",
        description: "Retrieves the details of a specific vehicle."
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'The ID of the vehicle',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: "Returns the vehicle details.",
        content: new Model(type: VehicleResponseDto::class)
    )]
    public function show(Vehicle $vehicle): JsonResponse
    {
        $vehicleDto = $this->vehicleService->findVehiclebyId($vehicle);

        return $this->json($vehicleDto);
    }

    /**
     *  Update vehicle (admin)
     */
    #[Route('update/{id}', name: 'update', methods: ['PUT'])]
    #[OA\Put(
        summary: "Update a vehicle (admin)",
        description: "Updates the details of a specific vehicle."
    )]
    #[OA\RequestBody(
        description: "The vehicle data to update.",
        required: true,
        content: new Model(type: UpdateVehicleDto::class)
    )]
    #[OA\Response(
        response: 200,
        description: "Vehicle updated successfully.",
        content: new Model(type: VehicleResponseDto::class)
    )]
    #[OA\Response(response: 404, description: "Vehicle not found.")]
    #[OA\Response(response: 409, description: "Conflict. The plate or VIN already exists.")]
    public function update(
        Vehicle $vehicle,
        #[MapRequestPayload] UpdateVehicleDto $dto
    ): JsonResponse {
        try {
            $responseDto = $this->vehicleService->updateVehicle($vehicle, $dto);
            return $this->json($responseDto);
        } catch (UniqueConstraintViolationException $e) {
            return $this->json(
                ['error' => 'Data conflict', 'message' => 'A vehicle with this license plate or VIN already exists.'],
                Response::HTTP_CONFLICT
            );
        } catch (\Exception $e) {
            return $this->json(
                ['error' => 'An unexpected error occurred', 'message' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Delete vehicle (admin)
     */
    #[Route('{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(
        summary: "Delete a vehicle (admin)",
        description: "Deletes a vehicle from the system."
    )]
    #[OA\Response(response: 204, description: "Vehicle deleted successfully.")]
    public function delete(Vehicle $vehicle): JsonResponse
    {
        $this->vehicleService->deleteVehicle($vehicle);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
