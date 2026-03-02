<?php

namespace App\Security;

use App\Entity\User\User as AppUser;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        // On cible ton User abstrait (Admin/Agent/Seller héritent tous de AppUser)
        if ($user instanceof AppUser && $user->getDeletedAt() !== null) {
            // Message affichable côté web (form_login) + bloquant côté API
            throw new CustomUserMessageAccountStatusException('Votre compte a été supprimé.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Rien à faire après authentification pour ce cas
    }
}
