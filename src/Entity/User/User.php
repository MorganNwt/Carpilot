<?php

/**
 * ==========================================
 * ============= ENTITÉ USER ================
 * ==========================================
 * 
 * Classe abstraite partagée entre Seller Agent et Admin
 */

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
// Une seule table pour tous les types
#[ORM\InheritanceType('SINGLE_TABLE')]
// Colonne qui ditsingue les types
#[ORM\DiscriminatorColumn(name: 'user_type', type: 'string')]
#[ORM\DiscriminatorMap([
    'seller' => Seller::class,
    'agent' => Agent::class,
    'admin' => Admin::class
])]
#[UniqueEntity(fields: ['email'], message: 'Email already used')]
#[ORM\HasLifecycleCallbacks]
abstract class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * ==========================================
     * PROPRIÉTÉS COMMUNES À TOUS LES UTILISATEURS
     * ==========================================
     */

    /**
     * Identifiant unique auto-généré
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id;

    /**
     * Identité de l'utilisateur
     */
    #[ORM\Column(length: 255)]
    protected ?string $firstName;

    #[ORM\Column(length: 255)]
    protected ?string $lastName;

    /**
     * Identifiant de connexion
     * Longueur 320 : standard RFC pour les emails
     */
    #[ORM\Column(length: 320, unique: true)]
    protected ?string $email;

    /**
     * Mot de passe hashé (jamais en clair)
     */
    #[ORM\Column(length: 255)]
    protected string $password;


    #[ORM\Column(length: 20)]
    private ?string $phone;

    /**
     * Rôles de sécurité Symfony (tableau JSON en base)
     */
    #[ORM\Column]
    protected array $roles = [];

    /**
     * Tiemestamp de création et de dernière modification
     */
    #[ORM\Column]
    protected ?\DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    protected ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column]
    private bool $isVerified = false;

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

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get the value of phone
     *
     * @return ?string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * Set the value of phone
     *
     * @param ?string $phone
     *
     * @return self
     */
    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
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
     * ==========================================
     * == MÉTHODES DE SÉCURITÉ | UserInterface ==
     * ==========================================
     */

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * ==========================================
     * ===== MÉTHODES UTILITAIRES COMMUNES ======
     * ==========================================
     */

    public function getFullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles());
    }

    /**
     * ==========================================
     * ========== MÉTHODES ABSTRAITES ===========
     * ==========================================
     */

    /**
     * Retourne le type d'utilisateur
     */
    abstract public function getUserTypeLabel(): string;

    /**
     * Représentation string de l'utilisateur
     */
    abstract public function __toString(): string;

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }


}