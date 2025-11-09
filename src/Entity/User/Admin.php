<?php

/**
 * ==========================================
 * ============= ENTITÉ ADMIN ===============
 * ==========================================
 */

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Admin extends User
{
    /**
     * ==========================================
     * ========== INFOS PRO ADMIN ===============
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
     * ========= MÉTHODES MÉTIER ================
     * ==========================================
     */


    /**
     * ==========================================
     * ==== MÉTHODES ABSTRAITES IMPLÉMENTÉES ====
     * ==========================================
     */

    public function getUserTypeLabel(): string
    {
        return 'Administrateur';
    }

    public function __toString(): string
    {
        return $this->getEmployeeId() ?? 'Administrateur';

    }
}