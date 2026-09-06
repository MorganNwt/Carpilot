<?php

namespace App\DataFixtures;

use App\Entity\User\Admin;
use App\Entity\User\Agent;
use App\Entity\User\Seller;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new Admin();
        $admin->setFirstName('Admin')
            ->setLastName('User')
            ->setEmail('admin@user.local')
            ->setPhone('0123456789');
        $admin->setEmployeeId('EMP-001');
        $admin->setRoles(['ROLE_ADMIN']);
        $password = $this->hasher->hashPassword($admin, 'pass_1234');
        $admin->setPassword($password);

        $manager->persist($admin);

        for ($i = 1; $i <= 2; $i++) {
            $agent = new Agent();

            $agent->setFirstName("AgentFirst{$i}");
            $agent->setLastName("AgentLast{$i}");
            $agent->setEmail("agent{$i}@carpilot.local");
            $agent->setPhone("+33 6 00 00 00 0{$i}");
            $plainAgentPassword = "AgentPass{$i}!";
            $hashedAgentPassword = $this->hasher->hashPassword($agent, $plainAgentPassword);
            $agent->setPassword($hashedAgentPassword);

            $agent->setRoles(['ROLE_AGENT']);


            $agent->setEmployeeId("AGT-00{$i}");

            $manager->persist($agent);
        }


        // Seller 1
        $seller1 = new Seller();
        $seller1->setFirstName('Bruno');
        $seller1->setLastName('Martin');
        $seller1->setEmail('bruno.seller@carpilot.local');
        $seller1->setPhone('+33 7 11 22 33 44');

        $plainSeller1Password = 'SellerOne!2025';
        $hashedSeller1Password = $this->hasher->hashPassword($seller1, $plainSeller1Password);
        $seller1->setPassword($hashedSeller1Password);

        $seller1->setRoles(['ROLE_SELLER']);
        $seller1->setAddress('10 Rue de la Paix');
        $seller1->setCity('Paris');
        $seller1->setPostalCode('75002');
        $seller1->setCountry('France');

        $manager->persist($seller1);

        // Seller 2
        $seller2 = new Seller();
        $seller2->setFirstName('Claire');
        $seller2->setLastName('Dubois');
        $seller2->setEmail('claire.seller@carpilot.local');
        $seller2->setPhone('+33 7 55 66 77 88');

        $plainSeller2Password = 'SellerTwo!2025';
        $hashedSeller2Password = $this->hasher->hashPassword($seller2, $plainSeller2Password);
        $seller2->setPassword($hashedSeller2Password);

        $seller2->setRoles(['ROLE_SELLER']);
        $seller2->setAddress('25 Avenue des Champs-Élysées');
        $seller2->setCity('Lyon');
        $seller2->setPostalCode('69001');
        $seller2->setCountry('France');

        $manager->persist($seller2);

        $manager->flush();

    }

}