<?php

namespace App\Controller\Api\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/api/admin/vehicle')]
final class VehicleController extends AbstractController
{
    #[Route('/', name: 'api_admin_vehicle_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        // TODO: Implement logic to list vehicles
        return $this->json(['message' => 'List vehicles']);
    }

    #[Route('/{id}', name: 'api_admin_vehicle_show', methods: ['GET'])]
    public function show(int $id): Response
    {
        // TODO: Implement logic to show a vehicle
        return $this->json(['message' => "Show vehicle $id"]);
    }

    #[Route('/', name: 'api_admin_vehicle_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        // TODO: Implement logic to create a vehicle
        return $this->json(['message' => 'Create vehicle']);
    }

    #[Route('/{id}', name: 'api_admin_vehicle_update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, int $id): Response
    {
        // TODO: Implement logic to update a vehicle
        return $this->json(['message' => "Update vehicle $id"]);
    }

    #[Route('/{id}', name: 'api_admin_vehicle_delete', methods: ['DELETE'])]
    public function delete(int $id): Response
    {
        // TODO: Implement logic to delete a vehicle
        return $this->json(['message' => "Delete vehicle $id"]);
    }
}
