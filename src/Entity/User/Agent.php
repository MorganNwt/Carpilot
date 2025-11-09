<?php

/**
 * ==========================================
 * ============ ENTITÉ AGENT ===============
 * ==========================================
 */

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
class Agent extends User
{
    /**
     * ==========================================
     * ============= INFOS PRO AGENT ============
     * ==========================================
     */

    #[ORM\Column(length: 50)]
    private ?string $employeeId;


    /**
     * ==========================================
     * ======== GETTERS ET SETTERS =============
     * ==========================================
     */

    public function getEmployeeId(): ?string
    {
        return $this->employeeId;
    }

    public function setEmployeeId(?string $employeeId): static
    {
        $this->employeeId = $employeeId;
        return $this;
    }


    /**
     * ==========================================
     * ==== MÉTHODES ABSTRAITES IMPLÉMENTÉES ====
     * ==========================================
     */

    public function getUserTypeLabel(): string
    {
        return 'Agent';
    }

    public function __toString(): string
    {
        return $this->getEmployeeId() ?? 'Agent';
    }

}