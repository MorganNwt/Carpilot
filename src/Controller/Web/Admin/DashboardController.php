<?php

namespace App\Controller\Web\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index()
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('admin/dashboard.html.twig');
    }
}
