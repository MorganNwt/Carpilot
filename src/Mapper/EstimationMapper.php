<?php

namespace App\Mapper;

use App\Entity\Estimation;
use App\DTO\Vehicle\EstimationResponseDto;


class EstimationMapper
{

    public function fromEntityToResponseDto(Estimation $estimation)
    {
        return new EstimationResponseDto(
            $estimation->getId(),
            $estimation->getStatus()->value,
            $estimation->getEstimatedPrice(),
            $estimation->getCreatedAt(),
            $estimation->getOfferPrice()
        );
    }
}
