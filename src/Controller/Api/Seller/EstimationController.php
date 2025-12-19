<?php

namespace App\Controller\Api\Seller;

use App\Entity\Estimation;
use App\Entity\User\Seller;
use App\Enum\EstimationStatus;
use App\DTO\Estimation\OfferPriceDto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/seller/estimations', name: 'api_seller_estimations_')]
final class EstimationController extends AbstractController
{
    #[Route('/{id}/offer', name: 'offer_price', methods: ['POST'])]
    #[IsGranted('ESTIMATION_OFFER', subject: 'estimation')]
    public function offerPrice(
        Estimation $estimation,
        #[MapRequestPayload] OfferPriceDto $dto,
        #[CurrentUser] Seller $seller,
        EntityManagerInterface $em
    ): JsonResponse {
        if ($estimation->getStatus() !== EstimationStatus::ESTIMATED) {
            return $this->json([
                'message' => 'This estimation cannot receive an offer.'
            ], 409);
        }

        $estimation->setOfferPrice((string) $dto->offer_price);
        $estimation->setStatus(EstimationStatus::OFFER_MADE);

        $em->flush();

        return $this->json([
            'message' => 'Offer price successfully submitted.',
            'offer_price' => $dto->offer_price
        ]);
    }
}
