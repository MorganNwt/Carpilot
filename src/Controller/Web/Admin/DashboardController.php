<?php

namespace App\Controller\Web\Admin;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(JWTTokenManagerInterface $jwtManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        // JWT utilisé uniquement pour appeler l'API depuis le dashboard
        $jwt = $jwtManager->create($user);

        return $this->render('admin/dashboard.html.twig', [
            'jwt' => $jwt,
        ]);
    }
}
