<?php

namespace App\Controller\Web\Agent;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

final class DashboardController extends AbstractController
{
    #[Route('/agent/dashboard', name: 'agent_dashboard', methods: ['GET'])]
    #[IsGranted('ROLE_AGENT')]
    public function index(JWTTokenManagerInterface $jwtManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }
        $jwt = $jwtManager->create($user);

        return $this->render('agent/dashboard.html.twig', [
            'jwt_token' => $jwt,
        ]);
    }
}
