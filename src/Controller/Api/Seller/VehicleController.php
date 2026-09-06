<?php

namespace App\Controller\Api\Seller;

use App\Entity\Vehicle;
use App\Entity\User\Seller;
use App\Mapper\VehicleMapper;

use OpenApi\Attributes as OA;
use App\DTO\Vehicle\UpdateVehicleDto;
use App\Enum\EstimationStatus;
use App\Service\Seller\VehicleService;
use App\DTO\Vehicle\VehicleResponseDto;
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
#[Route('/api/seller/vehicles/', name: 'api_vehicle_')]
#[OA\Tag(name: 'Vehicles')]
#[Security(name: 'bearerAuth')]
#[IsGranted('ROLE_SELLER')]
final class VehicleController extends AbstractController
{
    public function __construct(
        private readonly VehicleService $vehicleService,
        private readonly VehicleMapper $vehicleMapper
    ) {}


    #[Route('create-from-estimation', name: 'create_from_estimation', methods: ['POST'])]
    #[OA\Post(
        summary: "Create a vehicle from an estimation",
        description: "Creates a new vehicle based on a provided estimation token, if the token belongs to the authenticated seller."
    )]
    public function createFromEstimation(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof Seller) {
            return $this->json(
                ['message' => 'Accès refusé : vous devez être connecté avec un compte vendeur.'],
                Response::HTTP_FORBIDDEN
            );
        }

        $payload = $request->toArray();
        $token = $payload['estimation_token'] ?? null;

        if (!$token) {
            return $this->json(
                ['message' => 'estimation_token manquant.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $vehicle = $this->vehicleService->createVehicleFromEstimation($token, $user);
        $responseDto = $this->vehicleMapper->fromEntityToResponseDto($vehicle);

        return $this->json($responseDto, Response::HTTP_CREATED);
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
    public function index(
        #[CurrentUser] ?Seller $seller
    ) {
        if (!$seller) {
            return $this->json(['message' => 'Access denied.'], Response::HTTP_FORBIDDEN);
        }

        $vehicleDtos = $this->vehicleService->findVehiclesBySeller($seller);

        return $this->json($vehicleDtos);
    }

    #[Route('{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[OA\Get(
        summary: "Get a single vehicle's details",
        description: "Retrieves the details of a specific vehicle, if owned by the current seller."
    )]
    #[OA\Parameter(name: 'id', description: 'The ID of the vehicle to retrieve', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: "Returns the vehicle details.", content: new Model(type: VehicleResponseDto::class))]
    #[OA\Response(response: 403, description: "Access Denied (not the owner).")]
    #[OA\Response(response: 404, description: "Vehicle not found.")]
    public function show(
        Vehicle $vehicle,
        #[CurrentUser] ?Seller $seller
    ) {
        if (!$seller || $vehicle->getSeller()->getId() !== $seller->getId()) {
            return $this->json(['message' => 'Access denied. You are not the owner of this vehicle.'], Response::HTTP_FORBIDDEN);
        }

        $vehicleDto = $this->vehicleService->findVehiclebyId($vehicle);

        return $this->json($vehicleDto);
    }

    #[Route('update/{id}', name: 'update', methods: ['PUT'])]
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
    ) {
        if (!$seller || $vehicle->getSeller()->getId() !== $seller->getId()) {
            return $this->json(
                ['message' => 'Accès refusé : vous n’êtes pas le propriétaire de ce véhicule.'],
                Response::HTTP_FORBIDDEN
            );
        }

        // Verrouillage si dossier en cours / clôturé
        $estimation = $vehicle->getEstimation();
        if ($estimation) {
            $lockedStatuses = [
                EstimationStatus::IN_REVIEW,
                EstimationStatus::REJECTED,
                EstimationStatus::TRANSACTION_COMPLETED,
                EstimationStatus::CANCELLED,
            ];

            if (in_array($estimation->getStatus(), $lockedStatuses, true)) {
                return $this->json([
                    'message' => 'Véhicule verrouillé : impossible de modifier les informations car le dossier est en cours ou clôturé.',
                    'status' => $estimation->getStatus()->value,
                ], Response::HTTP_CONFLICT);
            }
        }

        try {
            $responseDto = $this->vehicleService->updateVehicle($vehicle, $dto);
            return $this->json($responseDto);
        } catch (UniqueConstraintViolationException $e) {
            return $this->json([
                'error' => 'Data conflict',
                'message' => 'Un véhicule avec cette plaque ou ce VIN existe déjà.'
            ], Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'An unexpected error occurred',
                'message' => 'Impossible de mettre à jour le véhicule.',
                'debug' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(
        summary: "Delete a vehicle",
        description: "Deletes a specific vehicle, if owned by the current seller."
    )]
    #[OA\Parameter(name: 'id', description: 'The ID of the vehicle to delete', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 204, description: "Vehicle deleted successfully.")]
    #[OA\Response(response: 403, description: "Access Denied (not the owner).")]
    #[OA\Response(response: 404, description: "Vehicle not found.")]
    public function delete(
        Vehicle $vehicle,
        #[CurrentUser] ?Seller $seller
    ) {
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
