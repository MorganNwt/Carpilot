<?php

namespace App\DataFixtures;

use App\Entity\User\Seller;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SellerFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 10; $i++) {
            $seller = new Seller();
            $seller->setFirstName($faker->firstName());
            $seller->setLastName($faker->lastName());
            $seller->setEmail($faker->unique()->safeEmail());
            $seller->setPhone($faker->phoneNumber());
            $seller->setPassword($this->hasher->hashPassword($seller, plainPassword: 'MotDePasse123!!!'));
            $seller->setRoles(['ROLE_SELLER']);

            // Champs supplémentaires :
            $seller->setAddress($faker->address());
            $seller->setCity($faker->city());
            $seller->setPostalCode($faker->postcode());
            $seller->setCountry($faker->country());
            $seller->setUpdatedAt(
                $faker->dateTimeBetween('-1 month', 'now') instanceof \DateTimeInterface
                    ? \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-1 month', 'now'))
                    : null
            );

            $manager->persist($seller);
        }

        $manager->flush();
    }
}
