<?php

namespace App\Controller\Api\Admin;

use App\DTO\Admin\UserResponseDto;
use App\Entity\User\Agent;
use App\Service\Admin\UserDeletionService;
use App\Service\Admin\AgentService;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Admin controller for Agent management.
 */
#[Route('/api/admin/agents', name: 'api_admin_agents_')]
#[IsGranted('ROLE_ADMIN')]
#[Security(name: 'bearerAuth')]
#[OA\Tag(name: 'Admin - User Management')]
final class AgentController extends AbstractController
{

    public function __construct(
        private readonly AgentService $userService
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(
        summary: "List all agents (paginated)",
        description: "Retrieves a paginated list of all users with the 'Agent' role. Requires ADMIN privileges."
    )]
    #[OA\Parameter(
        name: "page",
        in: "query",
        description: "The page number to retrieve.",
        schema: new OA\Schema(type: 'integer', default: 1)
    )]
    #[OA\Parameter(
        name: "limit",
        in: "query",
        description: "The number of items to retrieve per page.",
        schema: new OA\Schema(type: 'integer', default: 10)
    )]
    #[OA\Response(
        response: 200,
        description: "Returns the paginated list of agents.",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: UserResponseDto::class))
                ),
                new OA\Property(
                    property: 'meta',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'currentPage', type: 'integer'),
                        new OA\Property(property: 'totalPages', type: 'integer'),
                        new OA\Property(property: 'totalItems', type: 'integer'),
                        new OA\Property(property: 'limit', type: 'integer')
                    ]
                )
            ]
        )
    )]
    #[OA\Response(response: 403, description: "Forbidden. Access is denied.")]
    public function index(
        Request $request
    ) {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = min(100, $request->query->getInt('limit', 10));

        $paginatedAgents = $this->userService->getPaginatedAgents($page, $limit);

        return new JsonResponse($paginatedAgents);
    }
    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[OA\Delete(summary: 'Soft-delete a agent account')]
    #[OA\Response(response: 204, description: 'Account deleted. Associated records are preserved.')]
    #[OA\Response(response: 404, description: 'Account not found.')]
    public function delete(Agent $user, UserDeletionService $service): JsonResponse
    {
        $service->delete($user);

        return new JsonResponse(null, 204);
    }
}
