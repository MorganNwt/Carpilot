<?php

namespace App\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EstimationPageController extends AbstractController
{
    #[Route('/estimation', name: 'public_estimation_form', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('seller/formEstimation.html.twig');
    }
}
