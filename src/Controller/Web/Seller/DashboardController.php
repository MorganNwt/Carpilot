<?php

namespace App\Controller\Web\Seller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DashboardController extends AbstractController
{
    #[Route('/seller/dashboard', name: 'seller_dashboard')]
    public function index(): Response
    {
        // AUCUNE logique de sécurité ici
        // le contrôle d’accès est fait en JS + API
        return $this->render('seller/dashboard.html.twig');
    }
}
