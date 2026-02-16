<?php

/**
 * ==========================================
 * ============ ENTITÉ AGENT ===============
 * ==========================================
 */

namespace App\Entity\User;

use App\Entity\Appointment;
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
     * @var Collection<int, Appointment>
     */
    #[ORM\OneToMany(targetEntity: Appointment::class, mappedBy: 'agent')]
    private Collection $appointments;

    public function __construct()
    {
        parent::__construct();
        $this->appointments = new ArrayCollection();
    }


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

    /**
     * @return Collection<int, Appointment>
     */
    public function getAppointments(): Collection
    {
        return $this->appointments;
    }

    public function addAppointment(Appointment $appointment): static
    {
        if (!$this->appointments->contains($appointment)) {
            $this->appointments->add($appointment);
            $appointment->setAgent($this);
        }

        return $this;
    }

    public function removeAppointment(Appointment $appointment): static
    {
        if ($this->appointments->removeElement($appointment)) {
            // set the owning side to null (unless already changed)
            if ($appointment->getAgent() === $this) {
                $appointment->setAgent(null);
            }
        }

        return $this;
    }

}