<?php

namespace App\Controller\Web\Public;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EstimationPageController extends AbstractController
{
    /**
     * formEstimation function
     *
     * @return Response
     */
    #[Route('/estimation', name: 'public_estimation_form', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('seller/formEstimation.html.twig');
    }

    /**
     * Résultat de l’estimation (après calcul + création depuis token)
     */
    #[Route('/seller/estimation/result', name: 'seller_estimation_result', methods: ['GET'])]
    public function result(): Response
    {
        return $this->render('seller/estimationResult.html.twig');
    }
}
