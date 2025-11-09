<?php

namespace App\Service\Public;

use App\DTO\Public\EstimationRequestDto;
use App\Service\Vehicle\EstimationCalculatorService;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Service responsible for calculating vehicle estimation and caching the result.
 *
 * This service uses the EstimationCalculatorService to compute the price based on the provided
 * EstimationRequestDto. The result is cached using Symfony's FilesystemAdapter for one hour,
 * and a unique token is generated and returned for cache retrieval.
 *
 * @package App\Service\Public
 */
class EstimationService
{
    /**
     * Constructs a new instance of EstimationService.
     *
     * @param EstimationCalculatorService $calculator The service used to perform estimation calculations.
     */
    public function __construct(
        private readonly EstimationCalculatorService $calculator
    ) {
    }

    /**
     * Calculates the price estimation based on the provided EstimationRequestDto,
     * generates a unique token, and caches the result for 1 hour.
     *
     * @param EstimationRequestDto $dto Data transfer object containing estimation parameters.
     * @return string The generated cache token for retrieving the estimation result.
     */
    public function calculateAndCache(EstimationRequestDto $dto)
    {
        $price = $this->calculator->calculate($dto);

        $token = 'est_' . bin2hex(random_bytes(16));

        $cache = new FilesystemAdapter();

        $cache->get($token, function (ItemInterface $item) use ($price, $dto) {
            $item->expiresAfter(3600);

            return [
                'price' => $price,
                'vehicle_data' => $dto
            ];
        });

        return $token;
    }
}