<?php

namespace App\Service\Seller;

use App\DTO\Seller\ChangePasswordDto;
use App\DTO\Public\RegistrationDto;
use App\DTO\Seller\ProfileResponseDto;
use App\DTO\Seller\UpdateSellerDto;
use App\Entity\User\Seller;
use App\Mapper\UserMapper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;



/**
 * Class SellerService
 * Contains all business logic to interact with Seller entities.
 */
class ProfileService
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
     * Retrieves the public data for a given seller.
     *
     * @param Seller $seller The seller entity.
     * @return ProfileResponseDto A DTO containing safe-to-expose seller data.
     */
    public function getSeller(Seller $seller): ProfileResponseDto
    {
        return $this->mapper->fromSellerEntityToSellerProfileDto($seller);
    }

    /**
     * Updates an existing seller's profile data.
     *
     * @param Seller $seller The seller entity to update.
     * @param UpdateSellerDto $dto The DTO with the new data.
     * @return ProfileResponseDto The updated seller data as a DTO.
     */
    public function updateSeller(Seller $seller, UpdateSellerDto $dto): ProfileResponseDto
    {
        $this->mapper->fromUpdateSellerDtoToEntity($dto, $seller);

        $this->em->flush();

        return $this->mapper->fromSellerEntityToSellerProfileDto($seller);
    }

    /**
     * Deletes a seller's account from the database.
     *
     * @param Seller $seller The seller entity to delete.
     */
    public function deleteSeller(Seller $seller): void
    {
        $this->em->remove($seller);
        $this->em->flush();
    }

    /**
     * Changes a seller's password after verifying the current one.
     *
     * @param Seller $seller The seller whose password is to be changed.
     * @param ChangePasswordDto $dto The DTO containing the current and new passwords.
     * @throws UnprocessableEntityHttpException If the current password is invalid.
     */
    public function changePassword(Seller $seller, ChangePasswordDto $dto): void
    {
        if (
            !$this->passwordHasher->isPasswordValid(
                $seller,
                $dto->currentPassword
            )
        ) {
            throw new UnprocessableEntityHttpException('Invalid current password.');
        }

        $hashedPassword = $this->passwordHasher->hashPassword(
            $seller,
            $dto->newPassword
        );

        $seller->setPassword($hashedPassword);
        $this->em->persist($seller);
        $this->em->flush();
    }
}