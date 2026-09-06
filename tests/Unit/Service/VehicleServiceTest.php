<?php

namespace App\Tests\Unit\Service;

use App\Entity\Agency;
use App\Entity\User\Seller;
use App\Entity\Vehicle;
use App\Mapper\VehicleMapper;
use App\Repository\VehicleRepository;
use App\Service\Seller\VehicleService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class VehicleServiceTest extends TestCase
{
    private FilesystemAdapter $cache;
    private string $token;

    protected function setUp(): void
    {
        $this->cache = new FilesystemAdapter();
        $this->token = 'test_est_' . bin2hex(random_bytes(16));
        $item = $this->cache->getItem($this->token);
        $item->set(['price' => '12500.00', 'vehicle_data' => ['plate' => 'AA-112-AA']]);
        $item->expiresAfter(60);
        $this->cache->save($item);
    }

    protected function tearDown(): void
    {
        $this->cache->deleteItem($this->token);
    }

    public function testNewVehicleAndEstimationHaveSellerAgencyBeforeSaving(): void
    {
        $agency = new Agency();
        $seller = (new Seller())->setAgency($agency);
        $vehicle = new Vehicle();
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('persist')->with($vehicle);
        $em->expects(self::once())->method('flush')->willReturnCallback(function () use ($vehicle, $agency): void {
            self::assertSame($agency, $vehicle->getAgency());
            self::assertSame($agency, $vehicle->getEstimation()?->getAgency());
            self::assertSame($vehicle, $vehicle->getEstimation()?->getVehicle());
        });

        $service = $this->makeService($em, $vehicle);
        self::assertSame($vehicle, $service->createVehicleFromEstimation($this->token, $seller));
        self::assertFalse($this->cache->hasItem($this->token));
    }

    public function testMissingAgencyRejectsCreationBeforeWritingAndKeepsToken(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('persist');
        $em->expects(self::never())->method('flush');
        $service = $this->makeService($em, new Vehicle());

        try {
            $service->createVehicleFromEstimation($this->token, new Seller());
            self::fail('Creation without an agency must be rejected.');
        } catch (ConflictHttpException $exception) {
            self::assertSame(409, $exception->getStatusCode());
            self::assertTrue($this->cache->hasItem($this->token));
        }
    }

    public function testExistingVehicleKeepsItsAgencyWhenSellerAgencyDiffers(): void
    {
        $agency = new Agency();
        $seller = (new Seller())->setAgency(new Agency());
        (new \ReflectionProperty($seller, 'id'))->setValue($seller, 1);
        $vehicle = (new Vehicle())->setSeller($seller)->setAgency($agency);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('persist');
        $em->expects(self::once())->method('flush')->willReturnCallback(function () use ($vehicle, $agency): void {
            self::assertSame($agency, $vehicle->getAgency());
            self::assertSame($agency, $vehicle->getEstimation()?->getAgency());
        });

        $this->makeService($em, $vehicle, true)->createVehicleFromEstimation($this->token, $seller);
    }

    private function makeService(EntityManagerInterface $em, Vehicle $vehicle, bool $existing = false): VehicleService
    {
        $repository = $this->createMock(VehicleRepository::class);
        $repository->method('findOneBy')->willReturn($existing ? $vehicle : null);
        $mapper = $this->createMock(VehicleMapper::class);
        $mapper->method('fromCreateDtoToEntity')->willReturn($vehicle);

        return new VehicleService($em, $repository, $mapper);
    }
}
