<?php

namespace App\Controller\Api\Admin;

use App\Service\Admin\AdminService;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin/admins', name: 'api_admin_admins_')]
#[IsGranted('ROLE_ADMIN')]
#[Security(name: 'bearerAuth')]
#[OA\Tag(name: 'Admin - User Management')]
final class AdminController extends AbstractController
{
    public function __construct(
        private readonly AdminService $adminService
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(summary: 'List all admins (paginated)')]
    public function index(Request $request): JsonResponse
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = min(100, $request->query->getInt('limit', 20));

        $paginatedAdmins = $this->adminService->getPaginatedAdmins($page, $limit);

        return new JsonResponse($paginatedAdmins);
    }
}
