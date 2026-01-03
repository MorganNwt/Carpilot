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
        return $this->render('astuces/astuces.html.twig');
    }

    #[Route('/astuces/couleur-voiture', name: 'app_couleur_voiture')]
    public function couleurVoiture(): Response
    {
        return $this->render('astuces/couleurVoiture.html.twig');
    }

    #[Route('/astuces/reparer-voiture', name: 'app_reparer_voiture')]
    public function reparerVoiture(): Response
    {
        return $this->render('astuces/reparerVoiture.html.twig');
    }
}
