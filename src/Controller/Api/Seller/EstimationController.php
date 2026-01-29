<?php

namespace App\Controller\Api\Seller;

use App\Entity\Estimation;
use App\Enum\EstimationStatus;
use App\DTO\Estimation\OfferPriceDto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
        if ($estimation->getStatus() !== EstimationStatus::ESTIMATED) {
            return $this->json([
                'message' => 'This estimation cannot receive an offer.'
            ], 409);
        }

        // ✅ cohérent : si offer_price est float dans DTO, set float
        $estimation->setOfferPrice($dto->offer_price);

        $estimation->setStatus(EstimationStatus::OFFER_MADE);
        // si tu as un updatedAt :
        // $estimation->setUpdatedAt(new \DateTimeImmutable());

        $em->flush();

        return $this->json([
            'message' => 'Offer price successfully submitted.',
            'id' => $estimation->getId(),
            'status' => $estimation->getStatus()->value,
            'offer_price' => $estimation->getOfferPrice(),
            'estimated_price' => $estimation->getEstimatedPrice(),
        ]);
    }
}
