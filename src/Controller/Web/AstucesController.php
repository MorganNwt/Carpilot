<?php

namespace App\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AstucesController extends AbstractController
{
    #[Route('/astuces', name: 'app_astuces')]
    public function index(): Response
    {
        return $this->render('astuces.html.twig');
    }
}
