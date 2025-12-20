<?php

namespace App\Security\Voter;

use App\Entity\Estimation;
use App\Entity\User\Seller;
use App\Entity\User\Agent;
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

    private function canOffer(Estimation $estimation, UserInterface $user): bool
    {
        return $user instanceof Seller
            && $estimation->getStatus() === EstimationStatus::ESTIMATED;
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
