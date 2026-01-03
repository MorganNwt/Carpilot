<?php

namespace App\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EstimezMaintenantController extends AbstractController
{
    #[Route('/estimez-maintenant', name: 'app_estimez_maintenant')]
    public function index(): Response
    {
        return $this->render('estimezMaintenant.html.twig');
    }
}
