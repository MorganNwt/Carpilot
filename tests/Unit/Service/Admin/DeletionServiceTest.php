<?php

namespace App\Tests\Unit\Service\Admin;

use App\Entity\Appointment;
use App\Entity\Estimation;
use App\Entity\Notification;
use App\Entity\Transaction;
use App\Entity\User\Agent;
use App\Entity\User\Seller;
use App\Entity\Vehicle;
use App\Mapper\VehicleMapper;
use App\Repository\VehicleRepository;
use App\Service\Admin\AdminVehicleService;
use App\Service\Admin\UserDeletionService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class DeletionServiceTest extends TestCase
{
    public static function accountTypes(): iterable
    {
        yield 'seller' => [Seller::class];
        yield 'agent' => [Agent::class];
    }

    #[DataProvider('accountTypes')]
    public function testAccountDeletionPreservesRecordsAndIsIdempotent(string $class): void
    {
        $user = new $class();
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('remove');
        $em->expects(self::once())->method('flush');
        $service = new UserDeletionService($em);
        $service->delete($user);
        $deletedAt = $user->getDeletedAt();
        self::assertNotNull($deletedAt);
        $service->delete($user);
        self::assertSame($deletedAt, $user->getDeletedAt());
    }

    public static function linkedRecords(): iterable
    {
        yield 'transaction' => [true];
        yield 'appointment' => [false];
    }

    #[DataProvider('linkedRecords')]
    public function testVehicleWithLinkedRecordCannotBeDeleted(bool $transaction): void
    {
        $estimation = new Estimation();
        $vehicle = (new Vehicle())->setEstimation($estimation);
        if ($transaction) {
            $estimation->setTransaction(new Transaction());
        } else {
            $estimation->addAppointment(new Appointment());
        }
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::never())->method('remove');
        $em->expects(self::never())->method('flush');
        $this->expectException(ConflictHttpException::class);
        $this->vehicleService($em)->deleteVehicle($vehicle);
    }

    public function testVehicleDeletionDetachesNotificationsBeforeSaving(): void
    {
        $estimation = new Estimation();
        $notification = new Notification();
        $estimation->addNotification($notification);
        $vehicle = (new Vehicle())->setEstimation($estimation);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::once())->method('remove')->with($vehicle);
        $em->expects(self::once())->method('flush')->willReturnCallback(function () use ($notification): void {
            self::assertNull($notification->getEstimation());
        });
        $this->vehicleService($em)->deleteVehicle($vehicle);
    }

    private function vehicleService(EntityManagerInterface $em): AdminVehicleService
    {
        return new AdminVehicleService(
            $this->createMock(VehicleRepository::class),
            $this->createMock(VehicleMapper::class),
            $em,
        );
    }
}
