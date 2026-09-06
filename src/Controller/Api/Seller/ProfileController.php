<?php

namespace App\Controller\Api\Seller;

use App\DTO\Seller\ChangePasswordDto;
use App\DTO\Seller\ProfileResponseDto;
use App\DTO\Seller\UpdateSellerDto;
use App\Entity\User\Seller;
use App\Service\Seller\ProfileService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use InvalidArgumentException;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[OA\Tag(name: 'Sellers')]
#[Route('/api/sellers/profile', name: 'api_sellers_profile_')]
#[Security(name: 'bearerAuth')]
#[IsGranted('ROLE_SELLER')]
final class ProfileController extends AbstractController
{
    public function __construct(
        private readonly ProfileService $profileService
    ) {}

    #[Route('', name: 'get', methods: ['GET'])]
    #[OA\Get(
        summary: "Get current seller's profile",
        description: "Retrieves the public profile data of the currently authenticated seller, including their vehicles."
    )]
    #[OA\Response(
        response: 200,
        description: "Returns the seller's profile.",
        content: new Model(type: ProfileResponseDto::class)
    )]
    #[OA\Response(response: 403, description: "Access Denied.")]
    public function getProfile(
        #[CurrentUser] Seller $seller
    ) {
        $responseDto = $this->profileService->getSeller($seller);
        return new JsonResponse($responseDto);
    }

    #[Route('', name: 'update', methods: ['PUT'])]
    #[OA\Put(
        summary: "Update current seller's profile",
        description: "Allows the authenticated seller to update their own profile information."
    )]
    #[OA\RequestBody(
        description: "The seller data to update. Only non-null fields will be updated.",
        required: true,
        content: new Model(type: UpdateSellerDto::class)
    )]
    #[OA\Response(
        response: 200,
        description: "Profile updated successfully.",
        //content: new Model(type: SellerResponseDto::class)
    )]
    #[OA\Response(response: 403, description: "Access Denied.")]
    #[OA\Response(response: 409, description: "Conflict. The new email is already in use.")]
    #[OA\Response(response: 422, description: "Validation error.")]
    public function update(
        #[CurrentUser] Seller $seller,
        #[MapRequestPayload] UpdateSellerDto $dto
    ) {
        try {
            $responseDto = $this->profileService->updateSeller($seller, $dto);
            return new JsonResponse($responseDto);
        } catch (UniqueConstraintViolationException) {
            return new JsonResponse([
                'error' => 'Data conflict',
                'message' => 'This email is already in use.'
            ], Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'An unexpected error occurred',
                'message' => 'Could not update profile.',
                'debug' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('', name: 'delete', methods: ['DELETE'])]
   #[OA\Delete(
    summary: "Delete current seller's account",
    description: "Soft-deletes the authenticated seller account (deletedAt set). Associated data is preserved."
    )]
    #[OA\Response(response: 204, description: "Account deleted successfully.")]
    #[OA\Response(response: 403, description: "Access Denied.")]
    public function delete(
        #[CurrentUser] Seller $seller
    ) {
        try {
            $this->profileService->deleteSeller($seller);
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/change-password', name: 'change_password', methods: ['POST'])]
    #[OA\Post(
        summary: "Change current seller's password",
        description: "Allows the authenticated seller to change their password."
    )]
    #[OA\RequestBody(
        description: "Requires the current password for verification and the new password.",
        required: true,
        content: new Model(type: ChangePasswordDto::class)
    )]
    #[OA\Response(response: 200, description: "Password changed successfully.")]
    #[OA\Response(response: 400, description: "Bad Request (e.g., current password does not match).")]
    #[OA\Response(response: 403, description: "Access Denied.")]
    #[OA\Response(response: 422, description: "Validation Error.")]
    public function changePassword(
        #[CurrentUser] Seller $seller,
        #[MapRequestPayload] ChangePasswordDto $dto
    ): JsonResponse {
        try {
            $this->profileService->changePassword($seller, $dto);
            return new JsonResponse(['message' => 'Password successfully changed.']);
        } catch (InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
