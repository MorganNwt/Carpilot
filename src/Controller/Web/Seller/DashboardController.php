<?php

namespace App\Controller\Web\Seller;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class DashboardController extends AbstractController
{
    #[Route('/seller/dashboard', name: 'seller_dashboard')]
    #[IsGranted('ROLE_SELLER')]
    public function index(JWTTokenManagerInterface $jwtManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        // JWT utilisé uniquement pour appeler l'API depuis le dashboard
        $jwt = $jwtManager->create($user);

        return $this->render('seller/dashboard.html.twig', [
            'jwt_token' => $jwt,
        ]);
    }
}
