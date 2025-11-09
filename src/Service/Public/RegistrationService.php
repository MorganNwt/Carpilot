<?php

namespace App\Service\Public;

use App\DTO\Public\RegistrationDto;
use App\DTO\Seller\ProfileResponseDto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Mapper\UserMapper;

/**
 * Gère la logique de création de nouveaux comptes utilisateurs.
 */
final class RegistrationService
{
    /**
     * @param UserMapper $mapper The mapper for converting between Seller DTOs and entities.
     * @param UserPasswordHasherInterface $passwordHasher The service for hashing and verifying passwords.
     * @param EntityManagerInterface $em The entity manager for database operations.
     */
    public function __construct(
        private readonly UserMapper $mapper,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface $em,
    ) {
    }
    /**
     * Creates a new seller account, hashes the password, and returns a public DTO.
     *
     * @param RegistrationDto $dto The DTO containing the new seller's data.
     * @return ProfileResponseDto The DTO representing the newly created seller.
     */
    public function createSeller(RegistrationDto $dto): ProfileResponseDto
    {
        $seller = $this->mapper->fromRegistrationDtoToEntity($dto);

        $hashedPassword = $this->passwordHasher->hashPassword(
            $seller,
            $dto->password
        );

        $seller->setPassword($hashedPassword);

        $this->em->persist($seller);
        $this->em->flush();

        return $this->mapper->fromSellerEntityToSellerProfileDto($seller);
    }
}