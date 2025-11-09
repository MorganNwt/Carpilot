<?php

namespace App\Entity;

use App\Entity\Enum\EstimationStatus;
use App\Repository\EstimationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EstimationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Estimation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'estimation')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Vehicle $vehicle = null;

    #[ORM\Column(enumType: EstimationStatus::class)]
    private EstimationStatus $status = EstimationStatus::Estimated;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 0, nullable: true)]
    private ?string $estimatedPrice = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0, nullable: true)]
    private ?string $offerPrice = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * ==========================================
     * == CALLBACKS DOCTRINE (LIFECYCLE EVENTS) ==
     * ==========================================
     */

    /**
     * Callback avant persist : définit la date de création
     */
    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Callback avant update : met à jour la date de dernière modification
     */
    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    public function setVehicle(Vehicle $vehicle): static
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    public function getEstimatedPrice(): ?string
    {
        return $this->estimatedPrice;
    }

    public function setEstimatedPrice(?string $estimatedPrice): static
    {
        $this->estimatedPrice = $estimatedPrice;

        return $this;
    }

    public function getOfferPrice(): ?string
    {
        return $this->offerPrice;
    }

    public function setOfferPrice(?string $offerPrice): static
    {
        $this->offerPrice = $offerPrice;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }


    /**
     * Get the value of status
     *
     * @return EstimationStatus
     */
    public function getStatus(): EstimationStatus
    {
        return $this->status;
    }

    /**
     * Set the value of status
     *
     * @param EstimationStatus $status
     *
     * @return self
     */
    public function setStatus(EstimationStatus $status): self
    {
        $this->status = $status;

        return $this;
    }
}