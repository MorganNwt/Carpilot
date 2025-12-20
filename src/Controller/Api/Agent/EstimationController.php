<?php

namespace App\Controller\Api\Agent;

use App\Entity\Estimation;
use App\Enum\EstimationStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/agent/estimations', name: 'api_agent_estimations_')]
#[IsGranted('ROLE_AGENT')]
final class EstimationController extends AbstractController
{
    #[Route('/{id}/accept', name: 'accept', methods: ['POST'])]
    #[IsGranted('ESTIMATION_ACCEPT', subject: 'estimation')]
    public function accept(
        Estimation $estimation,
        EntityManagerInterface $em
    ): JsonResponse {
        $estimation->setStatus(EstimationStatus::TRANSACTION_COMPLETED);
        $em->flush();

        return $this->json([
            'message' => 'Estimation accepted'
        ]);
    }

    #[Route('/{id}/refuse', name: 'refuse', methods: ['POST'])]
    #[IsGranted('ESTIMATION_REFUSE', subject: 'estimation')]
    public function refuse(
        Estimation $estimation,
        EntityManagerInterface $em
    ): JsonResponse {
        $estimation->setStatus(EstimationStatus::CANCELLED);
        $em->flush();

        return $this->json([
            'message' => 'Estimation refused'
        ]);
    }
}
