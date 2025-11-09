<?php

namespace App\Service\Public;

use Psr\Log\LoggerInterface;
use App\Mapper\VehicleMapper;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class VehicleLookupService
{
    private const MOCK_API_BASE_URL = 'http://localhost:8001';

    public function __construct(

        private readonly HttpClientInterface $client,
        private readonly VehicleMapper $mapper,
        private readonly LoggerInterface $logger
    ){
    }

    public function lookupByPlate(string $plate)
    {
        try{
            $url = self::MOCK_API_BASE_URL . '/mock/plate-lookup/' . urldecode( $plate);

            $response = $this->client->request('GET', $url);

            $statusCode = $response->getStatusCode();

            if($statusCode === 404){
                return null;
            }

            $responseData = $response->toArray();

            return $this->mapper->fromApiToCreateVehicleDto( $responseData['data']);
        

        }catch(\Exception $e)
        {
            $this->logger->error('API failed',
            [
                'plate' => $plate,
                'server' => $e->getMessage()
            ]);

            throw $e;
        }
    }

}





