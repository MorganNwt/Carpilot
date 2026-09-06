<?php

namespace App\Entity;

use App\Entity\User\Seller;
use App\Entity\Agency;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\VehicleRepository;

#[ORM\Entity(repositoryClass: VehicleRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Vehicle
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 15, unique: true)]
    private ?string $plate = null;

    #[ORM\Column(type: 'string', length: 17, unique: true)]
    private ?string $vin = null;

    #[ORM\Column(type: 'string', length: 100)]
    private ?string $brand = null;

    #[ORM\Column(type: 'string', length: 100)]
    private ?string $model = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $version = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $energy = null;

    #[ORM\Column(type: 'integer')]
    private ?int $horsePower = null;

    #[ORM\Column(type: 'float')]
    private ?float $fiscalPower = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $gearBox = null;

    #[ORM\Column(type: 'integer')]
    private ?int $doors = null;

    #[ORM\Column(type: 'integer')]
    private ?int $seats = null;

    #[ORM\Column(type: 'string', length: 30)]
    private ?string $bodyType = null;

    #[ORM\Column(type: 'integer')]
    private ?int $weightKg = null;

    #[ORM\Column(type: 'string', length: 30)]
    private ?string $color = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $mileage = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $registrationDate = null;

    #[ORM\ManyToOne(targetEntity: Seller::class, inversedBy: 'vehicles')]
    #[ORM\JoinColumn('seller_id', 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Seller $seller = null;

    #[ORM\Column]
    protected ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    protected ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToOne(mappedBy: 'vehicle', cascade: ['persist', 'remove'])]
    private ?Estimation $estimation = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Agency $agency = null;


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
    /**
     * ==========================================
     * ===== GETTERS ET SETTERS COMMUNS ========
     * ==========================================
     */

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlate(): ?string
    {
        return $this->plate;
    }

    public function setPlate(string $plate): self
    {
        $this->plate = $plate;
        return $this;
    }

    public function getVin(): ?string
    {
        return $this->vin;
    }

    public function setVin(string $vin): self
    {
        $this->vin = $vin;
        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): self
    {
        $this->brand = $brand;
        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): self
    {
        $this->version = $version;
        return $this;
    }

    public function getEnergy(): ?string
    {
        return $this->energy;
    }

    public function setEnergy(string $energy): self
    {
        $this->energy = $energy;
        return $this;
    }

    public function getHorsePower(): ?int
    {
        return $this->horsePower;
    }

    public function setHorsePower(int $horsePower): self
    {
        $this->horsePower = $horsePower;
        return $this;
    }

    public function getFiscalPower(): ?float
    {
        return $this->fiscalPower;
    }

    public function setFiscalPower(float $fiscalPower): self
    {
        $this->fiscalPower = $fiscalPower;
        return $this;
    }

    public function getGearBox(): ?string
    {
        return $this->gearBox;
    }

    public function setGearBox(string $gearBox): self
    {
        $this->gearBox = $gearBox;
        return $this;
    }

    public function getDoors(): ?int
    {
        return $this->doors;
    }

    public function setDoors(int $doors): self
    {
        $this->doors = $doors;
        return $this;
    }

    public function getSeats(): ?int
    {
        return $this->seats;
    }

    public function setSeats(int $seats): self
    {
        $this->seats = $seats;
        return $this;
    }

    public function getMileage(): ?int
    {
        return $this->mileage;
    }

    public function setMileage(?int $mileage): self
    {
        $this->mileage   = $mileage;
        return $this;
    }


    public function getSeller()
    {
        return $this->seller;
    }

    public function setSeller(?Seller $seller)
    {
        $this->seller = $seller;
        return $this;
    }

    /**
     * Set the value of createdAt
     *
     * @param ?\DateTimeImmutable $createdAt
     *
     * @return self
     */
    public function setCreatedAt(?\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get the value of color
     *
     * @return ?string
     */
    public function getColor(): ?string
    {
        return $this->color;
    }

    /**
     * Set the value of color
     *
     * @param ?string $color
     *
     * @return self
     */
    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }


    /**
     * Get the value of registrationDate
     *
     * @return ?\DateTimeImmutable
     */
    public function getRegistrationDate(): ?\DateTimeImmutable
    {
        return $this->registrationDate;
    }

    /**
     * Set the value of registrationDate
     *
     * @param ?\DateTimeImmutable $registrationDate
     *
     * @return self
     */
    public function setRegistrationDate(?\DateTimeImmutable $registrationDate): self
    {
        $this->registrationDate = $registrationDate;

        return $this;
    }

    /**
     * Get the value of weightKg
     *
     * @return ?int
     */
    public function getWeightKg(): ?int
    {
        return $this->weightKg;
    }


    /**
     * Set the value of weightKg
     *
     * @param ?int $weightKg
     *
     * @return self
     */
    public function setWeightKg(?int $weightKg): self
    {
        $this->weightKg = $weightKg;

        return $this;
    }

    /**
     * Get the value of bodyType
     *
     * @return ?string
     */
    public function getBodyType(): ?string
    {
        return $this->bodyType;
    }

    /**
     * Set the value of bodyType
     *
     * @param ?string $bodyType
     *
     * @return self
     */
    public function setBodyType(?string $bodyType): self
    {
        $this->bodyType = $bodyType;

        return $this;
    }



    /**
     * Get the value of createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }



    /**
     * Get the value of updatedAt
     *
     * @return ?\DateTimeImmutable
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Set the value of updatedAt
     *
     * @param ?\DateTimeImmutable $updatedAt
     *
     * @return self
     */
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getEstimation(): ?Estimation
    {
        return $this->estimation;
    }

    public function setEstimation(?Estimation $estimation): static
    {
        $this->estimation = $estimation;

        if ($estimation && $estimation->getVehicle() !== $this) {
            $estimation->setVehicle($this);
        }

        return $this;
    }

    public function getAgency(): ?Agency
    {
        return $this->agency;
    }

    public function setAgency(?Agency $agency): static
    {
        $this->agency = $agency;

        return $this;
    }
}
