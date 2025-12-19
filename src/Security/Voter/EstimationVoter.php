<?php

namespace App\Security\Voter;

use App\Entity\Estimation;
use App\Entity\User\Seller;
use App\Entity\User\Agent;
use App\Enum\EstimationStatus;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EstimationVoter extends Voter
{
    public const OFFER = 'ESTIMATION_OFFER';
    public const ACCEPT = 'ESTIMATION_ACCEPT';
    public const REFUSE = 'ESTIMATION_REFUSE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
            self::OFFER,
            self::ACCEPT,
            self::REFUSE,
        ]) && $subject instanceof Estimation;
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token
    ): bool {
        $user = $token->getUser();
        $estimation = $subject;

        if (!$user) {
            return false;
        }

        return match ($attribute) {
            self::OFFER =>
            $user instanceof Seller
                && $estimation->getStatus() === EstimationStatus::ESTIMATED,

            self::ACCEPT,
            self::REFUSE =>
            $user instanceof Agent
                && $estimation->getStatus() === EstimationStatus::OFFER_MADE,

            default => false,
        };
    }
}
