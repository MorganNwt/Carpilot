<?php

namespace App\Tests\Unit\Service\Vehicle;

use App\DTO\Public\EstimationRequestDto;
use App\Service\Vehicle\EstimationCalculatorService;
use PHPUnit\Framework\TestCase;

class EstimationCalculatorServiceTest extends TestCase
{
    private function makeDto(string $registrationDate, int $mileage): EstimationRequestDto
    {
        $dto = new EstimationRequestDto();
        $dto->registrationDate = $registrationDate;
        $dto->mileage = $mileage;
        return $dto;
    }

    public function testCalculateAppliesYearsAndMileageAndRounds(): void
    {
        // now = 2026-03-03
        $service = new EstimationCalculatorService(new \DateTimeImmutable('2026-03-03'));

        // registration 2024-03-03 => 2 ans
        // price = 25000 - (2*1300) - (100000*0.08)
        //       = 25000 - 2600 - 8000 = 14400
        // round to hundred => 14400
        $dto = $this->makeDto('2024-03-03', 100000);

        $this->assertSame(14400.0, $service->calculate($dto));
    }

    public function testCalculateNeverGoesBelow1000(): void
    {
        $service = new EstimationCalculatorService(new \DateTimeImmutable('2026-03-03'));

        // très vieux + énorme km => prix négatif => doit retourner 1000
        $dto = $this->makeDto('1990-01-01', 500000);

        $this->assertSame(1000.0, $service->calculate($dto));
    }

    public function testCalculateRoundsToNearestHundred(): void
    {
        $service = new EstimationCalculatorService(new \DateTimeImmutable('2026-03-03'));

        // 0 an, 1 km => price = 25000 - 0 - 0.08 = 24999.92
        // round(-2) => 25000
        $dto = $this->makeDto('2026-03-03', 1);

        $this->assertSame(25000.0, $service->calculate($dto));
    }
}
