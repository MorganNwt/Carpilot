<?php

namespace App\Controller\Api\Seller;

use App\Entity\Vehicle;
use App\Entity\User\Seller;
use OpenApi\Attributes as OA;
use App\DTO\Vehicle\CreateVehicleDto;
use App\DTO\Vehicle\UpdateVehicleDto;
use App\Service\Seller\VehicleService;
use App\DTO\Vehicle\VehicleResponseDto;
use App\Mapper\VehicleMapper;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


/**
 * Handles CRUD operations for the authenticated Seller's vehicles.
 */
#[Route('/api/sellers/vehicles', name: 'api_vehicle_')]
#[OA\Tag(name: 'Vehicles')]
#[Security(name: 'bearerAuth')]
#[IsGranted('ROLE_SELLER')]
final class VehicleController extends AbstractController
{
    public function __construct(
        private readonly VehicleService $vehicleService,
        private readonly VehicleMapper $vehicleMapper
    ) {
    }

    /*
    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Post(
        summary: "Create a new vehicle",
        description: "Allows an authenticated seller to create a new vehicle and link it to their account."
    )]
    #[OA\RequestBody(
        description: "Data required to create a new vehicle",
        required: true,
        content: new Model(type: CreateVehicleDto::class)
    )]
    #[OA\Response(
        response: 201,
        description: "Vehicle created successfully",
        content: new Model(type: VehicleResponseDto::class)
    )]
    #[OA\Response(response: 403, description: "Access Denied (not authenticated).")]
    #[OA\Response(response: 409, description: "Conflict. A vehicle with the same plate or VIN already exists.")]
    #[OA\Response(response: 422, description: "Validation error. The request body is invalid.")]
    public function create(
        #[MapRequestPayload] CreateVehicleDto $dto,
        #[CurrentUser] ?Seller $seller
    ): JsonResponse {
        if (!$seller) {
            return $this->json(['message' => 'Forbidden access. You must be logged in as a seller.'], Response::HTTP_FORBIDDEN);
        }

        try {
            $responseDto = $this->vehicleService->createVehicle($dto, $seller);
            return $this->json($responseDto, Response::HTTP_CREATED);
        } catch (UniqueConstraintViolationException $e) {
            return $this->json(['error' => 'Data conflict', 'message' => 'A vehicle with this license plate or VIN already exists.'], Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return $this->json(['error' => 'An unexpected error occurred', 'message' => 'Could not create the vehicle.', 'debug' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
*/
    #[Route('create-from-estimation', name: 'create_from_estimation', methods: ['POST'])]
    public function createFromEstimation(Request $request, Seller $seller)
    {
        $token = $request->toArray()['estimation_token'] ?? null;

        if(!$token)
        {
            return new JsonResponse('Missing token', RESPONSE::HTTP_BAD_REQUEST);
        }

        $vehicle = $this->vehicleService->createVehicleFromEstimation($token, $seller);
        $responseDto = $this->vehicleMapper->fromEntityToResponseDto($vehicle);

        return new JsonResponse($responseDto, RESPONSE::HTTP_BAD_REQUEST);
    }


    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(
        summary: "List current seller's vehicles",
        description: "Retrieves a list of all vehicles owned by the currently authenticated seller."
    )]
    #[OA\Response(
        response: 200,
        description: "Returns the list of vehicles.",
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: VehicleResponseDto::class))
        )
    )]
    #[OA\Response(response: 403, description: "Access Denied (not authenticated).")]
    public function index(#[CurrentUser] ?Seller $seller): JsonResponse
    {
        if (!$seller) {
            return $this->json(['message' => 'Access denied.'], Response::HTTP_FORBIDDEN);
        }

        $vehicleDtos = $this->vehicleService->findVehiclesBySeller($seller);

        return $this->json($vehicleDtos);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[OA\Get(
        summary: "Get a single vehicle's details",
        description: "Retrieves the details of a specific vehicle, if owned by the current seller."
    )]
    #[OA\Parameter(name: 'id', description: 'The ID of the vehicle to retrieve', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: "Returns the vehicle details.", content: new Model(type: VehicleResponseDto::class))]
    #[OA\Response(response: 403, description: "Access Denied (not the owner).")]
    #[OA\Response(response: 404, description: "Vehicle not found.")]
    public function show(Vehicle $vehicle, #[CurrentUser] ?Seller $seller): JsonResponse
    {
        if (!$seller || $vehicle->getSeller()->getId() !== $seller->getId()) {
            return $this->json(['message' => 'Access denied. You are not the owner of this vehicle.'], Response::HTTP_FORBIDDEN);
        }

        $vehicleDto = $this->vehicleService->findVehiclebyId($vehicle);

        return $this->json($vehicleDto);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    #[OA\Put(
        summary: "Update a vehicle",
        description: "Updates the details of a specific vehicle, if owned by the current seller."
    )]
    #[OA\Parameter(name: 'id', description: 'The ID of the vehicle to update', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(description: "The vehicle data to update.", required: true, content: new Model(type: UpdateVehicleDto::class))]
    #[OA\Response(response: 200, description: "Vehicle updated successfully.", content: new Model(type: VehicleResponseDto::class))]
    #[OA\Response(response: 403, description: "Access Denied (not the owner).")]
    #[OA\Response(response: 404, description: "Vehicle not found.")]
    #[OA\Response(response: 409, description: "Conflict. The plate or VIN is already in use by another vehicle.")]
    #[OA\Response(response: 422, description: "Validation error. The request body is invalid.")]
    public function update(
        Vehicle $vehicle,
        #[MapRequestPayload] UpdateVehicleDto $dto,
        #[CurrentUser] ?Seller $seller
    ): JsonResponse {
        if (!$seller || $vehicle->getSeller()->getId() !== $seller->getId()) {
            return $this->json(['message' => 'Access denied. You are not the owner of this vehicle.'], Response::HTTP_FORBIDDEN);
        }

        try {
            $responseDto = $this->vehicleService->updateVehicle($vehicle, $dto);
            return $this->json($responseDto);
        } catch (UniqueConstraintViolationException $e) {
            return $this->json(['error' => 'Data conflict', 'message' => 'A vehicle with this license plate or VIN already exists.'], Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return $this->json(['error' => 'An unexpected error occurred', 'message' => 'Could not update the vehicle.', 'debug' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(
        summary: "Delete a vehicle",
        description: "Deletes a specific vehicle, if owned by the current seller."
    )]
    #[OA\Parameter(name: 'id', description: 'The ID of the vehicle to delete', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 204, description: "Vehicle deleted successfully.")]
    #[OA\Response(response: 403, description: "Access Denied (not the owner).")]
    #[OA\Response(response: 404, description: "Vehicle not found.")]
    public function delete(Vehicle $vehicle, #[CurrentUser] ?Seller $seller): JsonResponse
    {
        if (!$seller || $vehicle->getSeller()->getId() !== $seller->getId()) {
            return $this->json(['message' => 'Access denied. You are not the owner of this vehicle.'], Response::HTTP_FORBIDDEN);
        }

        try {
            $this->vehicleService->deleteVehicle($vehicle);
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}