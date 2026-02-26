<?php

namespace App\Controller\Web\Agent;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DashboardController extends AbstractController
{
    #[Route('/agent/dashboard', name: 'agent_dashboard', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('agent/dashboard.html.twig');
    }
}
