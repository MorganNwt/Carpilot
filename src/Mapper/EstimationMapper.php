<?php

namespace App\Mapper;

use App\Entity\Estimation;
use App\DTO\Vehicle\EstimationResponseDto;
use App\Enum\EstimationStatus;

class EstimationMapper
{
    public function fromEntityToResponseDto(Estimation $estimation): EstimationResponseDto
    {
        $canEditOffer = in_array($estimation->getStatus(), [
            EstimationStatus::ESTIMATED,
            EstimationStatus::OFFER_MADE,
        ], true);

        return new EstimationResponseDto(
            $estimation->getId(),
            $estimation->getStatus()->value,
            (float) $estimation->getEstimatedPrice(),
            $estimation->getCreatedAt(),
            $estimation->getOfferPrice(),
            $canEditOffer
        );
    }
}
