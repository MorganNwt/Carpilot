<?php

namespace App\Controller\Api\Seller;

use App\DTO\Estimation\OfferPriceDto;
use App\Entity\Estimation;
use App\Enum\EstimationStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/seller/estimations', name: 'api_seller_estimations_')]
final class EstimationController extends AbstractController
{
    #[Route('/{id}/offer', name: 'offer_price', methods: ['PUT'])]
    #[IsGranted('ESTIMATION_OFFER', subject: 'estimation')]
    public function offerPrice(
        Estimation $estimation,
        #[MapRequestPayload] OfferPriceDto $dto,
        EntityManagerInterface $em
    ): JsonResponse {
        /**
         * 1Vérification du statut
         * Seules les estimations en ESTIMATED ou OFFER_MADE
         * peuvent recevoir / modifier une offre.
         */
        $editableStatuses = [
            EstimationStatus::ESTIMATED,
            EstimationStatus::OFFER_MADE,
        ];

        if (!in_array($estimation->getStatus(), $editableStatuses, true)) {
            return $this->json([
                'message' => 'Cette offre ne peut plus être modifiée (en cours de traitement ou clôturée).'
            ], 409);
        }

        /**
         * DTO = string
         * On normalise la valeur et on la convertit en float
         * AVANT toute comparaison.
         */
        $raw = trim($dto->offer_price);
        $normalized = str_replace(',', '.', $raw);
        $offerPrice = (float) $normalized;

        /**
         * Validation numérique finale
         */
        if ($offerPrice <= 0) {
            return $this->json([
                'message' => 'Veuillez saisir un prix valide.'
            ], 422);
        }

        /**
         * 4 Mise à jour de l’offre
         * Stockage en float en base
         */
        $estimation->setOfferPrice($offerPrice);

        /**
         * 5️ Transition de statut
         * ESTIMATED → OFFER_MADE uniquement à la première soumission
         */
        if ($estimation->getStatus() === EstimationStatus::ESTIMATED) {
            $estimation->setStatus(EstimationStatus::OFFER_MADE);
        }

        /**
         * 6updatedAt Mise à jour de la date de modification
         */
        if (method_exists($estimation, 'setUpdatedAt')) {
            $estimation->setUpdatedAt(new \DateTimeImmutable());
        }

        $em->flush();

        /**
         * Réponse API
         */
        return $this->json([
            'message' => 'Offre enregistrée.',
            'estimation' => [
                'id' => $estimation->getId(),
                'status' => $estimation->getStatus()->value,
                'offer_price' => $estimation->getOfferPrice(),
                'estimated_price' => $estimation->getEstimatedPrice(),
            ],
        ], 200);
    }
}
