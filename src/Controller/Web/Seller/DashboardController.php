<?php

namespace App\Controller\Web\Seller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class DashboardController extends AbstractController
{
    #[Route('/seller/dashboard', name: 'seller_dashboard')]
    #[IsGranted('ROLE_SELLER')]
    public function index()
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('seller/dashboard.html.twig');
    }
}
