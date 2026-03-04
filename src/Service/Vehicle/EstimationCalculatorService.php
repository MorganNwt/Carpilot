<?php

namespace App\Service\Vehicle;

use App\DTO\Public\EstimationRequestDto;

/**
 * EstimationCalculatorService handles the calculation logic for vehicle estimations.
 *
 * This service provides methods to estimate various parameters related to vehicles,
 * such as pricing, depreciation, and other relevant metrics.
 *
 * @package App\Service\Vehicle
 */

class EstimationCalculatorService
{

    /**
     * Summary of __construct
     * @param mixed $now
     */
    public function __construct(private ?\DateTimeImmutable $now = null)
    {
        $this->now = $this->now ?? new \DateTimeImmutable();
    }


    /**
     * Calculates the estimated price of a vehicle based on its registration date and mileage.
     *
     * The calculation subtracts a depreciation value per year since registration and per mileage unit from a base price.
     * Ensures the returned price is not less than 1000 and rounds to the nearest hundred.
     *
     * @param EstimationRequestDto $dto Data transfer object containing vehicle registration date and mileage.
     * @return float Estimated price of the vehicle.
     */
    public function calculate(EstimationRequestDto $dto): float
    {
        $registration = new \DateTimeImmutable($dto->registrationDate);
        $years = $this->now->diff($registration)->y;

        $price = 25000 - ($years * 1300) - ($dto->mileage * 0.08);

        return max(1000, round($price, -2));
    }
}
