<?php

namespace App\Controller\Api\Agent;

use App\Entity\Estimation;
use App\Enum\EstimationStatus;
use App\Service\Agent\AgentEstimationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/agent/estimations', name: 'api_agent_estimations_')]
#[IsGranted('ROLE_AGENT')]
final class EstimationController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request, AgentEstimationService $service): JsonResponse
    {
        $status = $request->query->get('status'); // "offer_made", "in_review", ...

        //  Renvoie des VehicleResponseDto (avec sellerFirstName/sellerLastName + estimation)
        return $this->json($service->listByStatus($status));
    }

    #[Route('/{id}/review', name: 'review', methods: ['POST'])]
    #[IsGranted('ESTIMATION_ACCEPT', subject: 'estimation')]
    public function review(Estimation $estimation, EntityManagerInterface $em): JsonResponse
    {
        if ($estimation->getStatus() !== EstimationStatus::OFFER_MADE) {
            return $this->json([
                'message' => 'Impossible de prendre en charge : statut invalide.',
                'status' => $estimation->getStatus()->value,
            ], Response::HTTP_CONFLICT);
        }

        $estimation->setStatus(EstimationStatus::IN_REVIEW);

        if (method_exists($estimation, 'setUpdatedAt')) {
            $estimation->setUpdatedAt(new \DateTimeImmutable());
        }

        $em->flush();

        return $this->json(['message' => 'Dossier pris en charge.']);
    }

    #[Route('/{id}/accept', name: 'accept', methods: ['POST'])]
    #[IsGranted('ESTIMATION_ACCEPT', subject: 'estimation')]
    public function accept(Estimation $estimation, EntityManagerInterface $em): JsonResponse
    {
        if (!in_array($estimation->getStatus(), [EstimationStatus::OFFER_MADE, EstimationStatus::IN_REVIEW], true)) {
            return $this->json([
                'message' => 'Impossible d’accepter : statut invalide.',
                'status' => $estimation->getStatus()->value,
            ], Response::HTTP_CONFLICT);
        }

        $estimation->setStatus(EstimationStatus::TRANSACTION_COMPLETED);

        if (method_exists($estimation, 'setUpdatedAt')) {
            $estimation->setUpdatedAt(new \DateTimeImmutable());
        }

        $em->flush();

        return $this->json(['message' => 'Estimation acceptée.']);
    }

    #[Route('/{id}/reject', name: 'reject', methods: ['POST'])]
    #[IsGranted('ESTIMATION_REFUSE', subject: 'estimation')]
    public function reject(Estimation $estimation, EntityManagerInterface $em): JsonResponse
    {
        if (!in_array($estimation->getStatus(), [EstimationStatus::OFFER_MADE, EstimationStatus::IN_REVIEW], true)) {
            return $this->json([
                'message' => 'Impossible de refuser : statut invalide.',
                'status' => $estimation->getStatus()->value,
            ], Response::HTTP_CONFLICT);
        }

        $estimation->setStatus(EstimationStatus::REJECTED);

        if (method_exists($estimation, 'setUpdatedAt')) {
            $estimation->setUpdatedAt(new \DateTimeImmutable());
        }

        $em->flush();

        return $this->json(['message' => 'Estimation refusée.']);
    }
}
