<?php

namespace App\Controller\Api\Admin;

use App\Service\Admin\AdminService;
use App\Service\Admin\AdminDashboardService;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin', name: 'api_admin_')]
#[IsGranted('ROLE_ADMIN')]
#[Security(name: 'bearerAuth')]
#[OA\Tag(name: 'Admin - User Management')]
final class AdminController extends AbstractController
{
    public function __construct(
        private readonly AdminService $adminService
    ) {}

    #[Route('/dashboard', name: 'dashboard', methods: ['GET'])]
    public function dashboard(AdminDashboardService $service): JsonResponse
    {
        return $this->json($service->getDashboard());
    }

    #[Route('/admins', name: 'admins_list', methods: ['GET'])]
    #[OA\Get(summary: 'List all admins (paginated)')]
    #[OA\Parameter(
        name: 'page',
        in: 'query',
        description: 'Page number for pagination (default: 1)',
        schema: new OA\Schema(type: 'integer', default: 1)
    )]
    #[OA\Parameter(
        name: 'limit',
        in: 'query',
        description: 'Number of items per page for pagination (default: 20, max: 100)',
        schema: new OA\Schema(type: 'integer', default: 20)
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns a paginated list of admins.',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'id', type: 'integer'),
                            new OA\Property(property: 'email', type: 'string'),
                            new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string')),
                        ]
                    )
                ),
                new OA\Property(property: 'meta', type: 'object', properties: [
                    new OA\Property(property: 'current_page', type: 'integer'),
                    new OA\Property(property: 'total_pages', type: 'integer'),
                    new OA\Property(property: 'total_items', type: 'integer'),
                ]),
            ]
        )
    )]
    public function listAdmins(Request $request): JsonResponse
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = min(100, $request->query->getInt('limit', 20));

        $paginatedAdmins = $this->adminService->getPaginatedAdmins($page, $limit);

        return new JsonResponse($paginatedAdmins);
    }
}
