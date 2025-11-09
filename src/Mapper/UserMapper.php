<?php

namespace App\Mapper;

use App\DTO\Admin\UserResponseDto;
use App\DTO\Public\RegistrationDto;
use App\DTO\Seller\ProfileResponseDto;
use App\DTO\Seller\UpdateSellerDto;
use App\Entity\User\Admin;
use App\Entity\User\Agent;
use App\Entity\User\Seller;
use App\Entity\User\User;
use App\Mapper\VehicleMapper;

/**
 * Polymorphic mapper that can transform any User entity
 * into the appropriate DTO based on the context, and vice-versa.
 */
class UserMapper
{
    public function __construct(
        private readonly VehicleMapper $vehicleMapper
    ) {
    }

    /**
     * Maps any User entity to a generic DTO for the admin panel.
     */
    public function fromEntityToResponseDto(User $user): UserResponseDto
    {
        $userType = match (true) {
            $user instanceof Seller => 'seller',
            $user instanceof Agent => 'agent',
            $user instanceof Admin => 'admin',
            default => 'user',
        };

        // Initialize specific fields to null or empty
        $employeeId = null;
        $fullAddress = null;
        $vehiclesDto = [];

        // Populate fields based on the actual class of the user
        if ($user instanceof Agent || $user instanceof Admin) {
            $employeeId = $user->getEmployeeId();
        }

        if ($user instanceof Seller) {
            $fullAddress = $user->getFullAddress();

            // Les véhicules sont maintenant toujours mappés pour un vendeur.
            foreach ($user->getVehicles() as $vehicle) {
                $vehiclesDto[] = $this->vehicleMapper->fromEntityToResponseDto($vehicle);
            }
        }

        return new UserResponseDto(
            id: $user->getId(),
            userType: $userType,
            firstName: $user->getFirstName(),
            lastName: $user->getLastName(),
            email: $user->getEmail(),
            phone: $user->getPhone(),
            roles: $user->getRoles(),
            employeeId: $employeeId,
            fullAddress: $fullAddress,
            createdAt: $user->getCreatedAt(),
            vehicles: $vehiclesDto
        );
    }

    /**
     * Maps a Seller entity to a detailed DTO for their own profile view.
     */
    public function fromSellerEntityToSellerProfileDto(Seller $seller): ProfileResponseDto
    {
        $vehicleDtos = [];
        foreach ($seller->getVehicles() as $vehicle) {
            $vehicleDtos[] = $this->vehicleMapper->fromEntityToResponseDto($vehicle);
        }

        return new ProfileResponseDto(
            id: $seller->getId(),
            firstName: $seller->getFirstName(),
            lastName: $seller->getLastName(),
            email: $seller->getEmail(),
            phone: $seller->getPhone(),
            address: $seller->getAddress(),
            city: $seller->getCity(),
            postalCode: $seller->getPostalCode(),
            country: $seller->getCountry(),
            createdAt: $seller->getCreatedAt(),
            vehicles: $vehicleDtos
        );
    }

    /**
     * Transforms a RegistrationDto into a new Seller entity.
     * Note: Password hashing is handled in the service layer.
     */
    public function fromRegistrationDtoToEntity(RegistrationDto $dto): Seller
    {
        $seller = new Seller();
        $seller->setFirstName($dto->firstName);
        $seller->setLastName($dto->lastName);
        $seller->setEmail($dto->email);
        $seller->setPhone($dto->phone);
        $seller->setAddress($dto->address);
        $seller->setCity($dto->city);
        $seller->setPostalCode($dto->postalCode);
        $seller->setCountry($dto->country);

        return $seller;
    }

    /**
     * Applies changes from an UpdateSellerDto to an existing Seller entity.
     */
    public function fromUpdateSellerDtoToEntity(UpdateSellerDto $dto, Seller $seller): Seller
    {
        if ($dto->firstName !== null) {
            $seller->setFirstName($dto->firstName);
        }
        if ($dto->lastName !== null) {
            $seller->setLastName($dto->lastName);
        }
        if ($dto->email !== null) {
            $seller->setEmail($dto->email);
        }
        if ($dto->phone !== null) {
            $seller->setPhone($dto->phone);
        }
        if ($dto->address !== null) {
            $seller->setAddress($dto->address);
        }
        if ($dto->city !== null) {
            $seller->setCity($dto->city);
        }
        if ($dto->postalCode !== null) {
            $seller->setPostalCode($dto->postalCode);
        }
        if ($dto->country !== null) {
            $seller->setCountry($dto->country);
        }

        return $seller;
    }


}