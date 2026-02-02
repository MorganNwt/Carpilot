<?php

namespace App\Security\Voter;

use App\Entity\Estimation;
use App\Entity\User\Agent;
use App\Entity\User\Seller;
use App\Enum\EstimationStatus;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class EstimationVoter extends Voter
{
    public const OFFER  = 'ESTIMATION_OFFER';
    public const ACCEPT = 'ESTIMATION_ACCEPT';
    public const REFUSE = 'ESTIMATION_REFUSE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Estimation
            && in_array($attribute, [self::OFFER, self::ACCEPT, self::REFUSE], true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Estimation $estimation */
        $estimation = $subject;

        return match ($attribute) {
            self::OFFER  => $this->canOffer($estimation, $user),
            self::ACCEPT => $this->canAccept($estimation, $user),
            self::REFUSE => $this->canRefuse($estimation, $user),
            default      => false,
        };
    }

    private function canOffer(Estimation $estimation, UserInterface $user): bool
    {
        if (!$user instanceof Seller) {
            return false;
        }

        // L'estimation doit appartenir au vendeur connecté
        $vehicle = $estimation->getVehicle();
        if (!$vehicle || !$vehicle->getSeller()) {
            return false;
        }

        if ($vehicle->getSeller()->getId() !== $user->getId()) {
            return false;
        }

        //  statut : modifiable uniquement si pas verrouillé
        return in_array($estimation->getStatus(), [
            EstimationStatus::ESTIMATED,
            EstimationStatus::OFFER_MADE,
        ], true);
    }

    private function canAccept(Estimation $estimation, UserInterface $user): bool
    {
        return $user instanceof Agent
            && $estimation->getStatus() === EstimationStatus::OFFER_MADE;
    }

    private function canRefuse(Estimation $estimation, UserInterface $user): bool
    {
        return $user instanceof Agent
            && $estimation->getStatus() === EstimationStatus::OFFER_MADE;
    }
}
