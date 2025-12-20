<?php

namespace App\Service\Public;

use Psr\Log\LoggerInterface;
use App\Mapper\VehicleMapper;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\External\MockApiPlate\Data\MockPlateDataProvider;
use App\External\MockApiPlate\Dto\PlateLookupResponseDto;

class VehicleLookupService
{
    //private const MOCK_API_BASE_URL = 'http://localhost:8001';

    public function __construct(

        // private readonly HttpClientInterface $client,
        // private readonly VehicleMapper $mapper,
        // private readonly LoggerInterface $logger
        private readonly MockPlateDataProvider $provider
    ) {}

    // // Pour lancer le second server simulant l'API externe : php -S localhost:8001 -t public
    // public function lookupByPlate(string $plate)
    // {
    //     try {
    //         $url = self::MOCK_API_BASE_URL . '/mock/plate-lookup/' . urldecode($plate);

    //         $response = $this->client->request('GET', $url);

    //         $statusCode = $response->getStatusCode();

    //         if ($statusCode === 404) {
    //             return null;
    //         }

    //         $responseData = $response->toArray();

    //         return $this->mapper->fromApiToCreateVehicleDto($responseData['data']);
    //     } catch (\Exception $e) {
    //         $this->logger->error(
    //             'API failed',
    //             [
    //                 'plate' => $plate,
    //                 'server' => $e->getMessage()
    //             ]
    //         );

    //         throw $e;
    //     }
    // }

    // Sans appel HTTP, utilisation du provider mock interne
    public function lookupByPlate(string $plate): PlateLookupResponseDto
    {
        return $this->provider->findByPlate($plate);
    }
}
