<?php

namespace App\Service\Admin;

use App\Entity\User\Agent;
use App\Entity\User\Seller;
use Doctrine\ORM\EntityManagerInterface;

final class UserDeletionService
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    public function delete(Seller|Agent $user): void
    {
        if ($user->getDeletedAt() !== null) {
            return;
        }

        $user->softDelete();
        $this->em->flush();
    }
}
