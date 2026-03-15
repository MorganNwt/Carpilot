<?php

namespace App\Controller\Web\Agent;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class DashboardController extends AbstractController
{
    #[Route('/agent/dashboard', name: 'agent_dashboard', methods: ['GET'])]
    #[IsGranted('ROLE_AGENT')]
    public function index()
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }
       
        return $this->render('agent/dashboard.html.twig');
    }
}
