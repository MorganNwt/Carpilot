<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Doctrine mappe DECIMAL en string pour éviter les erreurs de précision
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $finalPrice = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $transactionDate;

    #[ORM\Column(length: 50)]
    private ?string $paymentStatus = null;

    // 1 Estimation <-> 1 Transaction (virement final)
    #[ORM\OneToOne(inversedBy: 'transaction', targetEntity: Estimation::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Estimation $estimation = null;

    public function __construct()
    {
        $this->transactionDate = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFinalPrice(): ?string
    {
        return $this->finalPrice;
    }

    public function setFinalPrice(string $finalPrice): static
    {
        $this->finalPrice = $finalPrice;
        return $this;
    }

    public function getTransactionDate(): \DateTimeImmutable
    {
        return $this->transactionDate;
    }

    public function setTransactionDate(\DateTimeImmutable $transactionDate): static
    {
        $this->transactionDate = $transactionDate;
        return $this;
    }

    public function getPaymentStatus(): ?string
    {
        return $this->paymentStatus;
    }

    public function setPaymentStatus(string $paymentStatus): static
    {
        $this->paymentStatus = $paymentStatus;
        return $this;
    }

    public function getEstimation(): ?Estimation
    {
        return $this->estimation;
    }

    public function setEstimation(?Estimation $estimation): static
    {
        $this->estimation = $estimation;
        return $this;
    }
}
