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
            && in_array($attribute, [
                self::OFFER,
                self::ACCEPT,
                self::REFUSE,
            ], true);
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token
    ): bool {
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

    /**
     * ADMIN = agent+
     */
    private function isAdmin(UserInterface $user): bool
    {
        return method_exists($user, 'getRoles')
            && in_array('ROLE_ADMIN', $user->getRoles(), true);
    }

    /**
     * Le vendeur peut proposer / modifier son offre uniquement si :
     * - il est propriétaire du véhicule
     * - le statut n’est pas verrouillé
     */
    private function canOffer(Estimation $estimation, UserInterface $user): bool
    {
        if (!$user instanceof Seller) {
            return false;
        }

        // Ownership : l’estimation doit appartenir au vendeur connecté
        $vehicle = $estimation->getVehicle();
        if (!$vehicle || !$vehicle->getSeller()) {
            return false;
        }

        if ($vehicle->getSeller()->getId() !== $user->getId()) {
            return false;
        }

        // Statuts modifiables par le vendeur
        return in_array($estimation->getStatus(), [
            EstimationStatus::ESTIMATED,
            EstimationStatus::OFFER_MADE,
        ], true);
    }

    /**
     * Agent ou Admin peut accepter une estimation
     */
    private function canAccept(Estimation $estimation, UserInterface $user): bool
    {
        if (!($user instanceof Agent) && !$this->isAdmin($user)) {
            return false;
        }

        return in_array($estimation->getStatus(), [
            EstimationStatus::OFFER_MADE,
            EstimationStatus::IN_REVIEW,
        ], true);
    }

    /**
     * Agent ou Admin peut refuser une estimation
     */
    private function canRefuse(Estimation $estimation, UserInterface $user): bool
    {
        if (!($user instanceof Agent) && !$this->isAdmin($user)) {
            return false;
        }

        return in_array($estimation->getStatus(), [
            EstimationStatus::OFFER_MADE,
            EstimationStatus::IN_REVIEW,
        ], true);
    }
}
