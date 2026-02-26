<?php

namespace App\Controller\Web\Admin;

use App\Service\Admin\AdminDashboardService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard', methods: ['GET'])]
    public function index(AdminDashboardService $dashboardService): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'dashboard' => $dashboardService->getDashboard(),
        ]);
    }
}
