<?php

/**
 * ==========================================
 * ============= ENTITÉ SELLER ==============
 * ==========================================
 */

namespace App\Entity\User;

use App\Entity\Vehicle;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity]
class Seller extends User
{
    /**
     * ==========================================
     * ========== CONTACT DU VENDEUR ============
     * ==========================================
     */


    #[ORM\Column(length: 500)]
    private ?string $address;

    #[ORM\Column(length: 100)]
    private ?string $city;

    #[ORM\Column(length: 10)]
    private ?string $postalCode;

    #[ORM\Column(length: 100)]
    private ?string $country;

    #[ORM\OneToMany(mappedBy: 'seller', targetEntity: Vehicle::class, cascade: ['remove'])]
    private collection $vehicles;

    public function __construct()
    {
        $this->vehicles = new ArrayCollection();
        $this->setRoles(['ROLE_SELLER']);
    }

    /**
     * ==========================================
     * ========== GETTERS ET SETTERS ============
     * ==========================================
     */


    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): static
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;
        return $this;
    }

    public function getVehicles()
    {
        return $this->vehicles;
    }

    /**
     * ==========================================
     * ========= MÉTHODES UTILITAIRES ===========
     * ==========================================
     */

    public function getFullAddress(): string
    {
        $parts = array_filter([
            $this->address,
            $this->postalCode . ' ' . $this->city,
            $this->country
        ]);

        return implode(', ', $parts);
    }

    /**
     * ==========================================
     * ==== MÉTHODES ABSTRAITES IMPLÉMENTÉES ====
     * ==========================================
     */

    public function getUserTypeLabel(): string
    {
        return 'Vendeur particulier';
    }

    public function __toString(): string
    {
        return $this->getFullName() . ' - Vendeur (' . $this->email . ')';
    }
}