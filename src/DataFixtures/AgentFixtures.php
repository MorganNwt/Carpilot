<?php

namespace App\DataFixtures;

use App\Entity\User\Agent;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AgentFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 7; $i++) {
            $agent = new Agent();
            $agent->setFirstName($faker->firstName());
            $agent->setLastName($faker->lastName());
            $agent->setEmail($faker->unique()->safeEmail());
            $agent->setPhone($faker->phoneNumber());
            $agent->setPassword($this->hasher->hashPassword($agent, plainPassword: 'agent123'));
            $agent->setRoles(['ROLE_AGENT']);

            // Champs supplémentaires
            $agent->setEmployeeId($faker->unique()->bothify('AGT###'));
            $agent->setUpdatedAt(
                \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-3 months', 'now'))
            );

            $manager->persist($agent);
        }

        $manager->flush();
    }
}