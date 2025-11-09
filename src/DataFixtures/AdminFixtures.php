<?php

namespace App\DataFixtures;

use App\Entity\User\Admin;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 5; $i++) {
            $admin = new Admin();
            $admin->setFirstName($faker->firstName());
            $admin->setLastName($faker->lastName());
            $admin->setEmail($faker->unique()->safeEmail());
            $admin->setPhone($faker->phoneNumber());
            $admin->setPassword($this->hasher->hashPassword($admin, 'admin123'));
            $admin->setRoles(['ROLE_ADMIN']);

            // Champs supplémentaires
            $admin->setEmployeeId($faker->unique()->bothify('ADM###'));
            $admin->setUpdatedAt(
                \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-2 months', 'now'))
            );

            $manager->persist($admin);
        }

        $manager->flush();
    }
}