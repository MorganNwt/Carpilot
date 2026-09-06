<?php

namespace App\Controller\Web\Public;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RGPDController extends AbstractController
{
    #[Route('/rgpd', name: 'app_legal_rgpd')]
    public function index(): Response
    {
        return $this->render('legal/rgpd.html.twig');
    }
}
