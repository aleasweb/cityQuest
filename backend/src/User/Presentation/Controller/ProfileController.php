<?php

declare(strict_types=1);

namespace App\User\Presentation\Controller;

use App\User\Application\DTO\UpdateProfileRequest;
use App\User\Application\Service\ProfileService;
use App\User\Domain\Entity\User;
use App\User\Domain\Exception\UserAlreadyExistsException;
use App\User\Domain\Exception\UserNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProfileController extends AbstractController
{
    public function __construct(
        private ProfileService $profileService,
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * Получить свой профиль (требует JWT аутентификации).
     */
    #[Route('/api/user/profile', name: 'api_user_profile', methods: ['GET'])]
    public function getProfile(#[CurrentUser] User $user): JsonResponse
    {
        $profile = $this->profileService->getProfile($user);

        return $this->json($profile);
    }

    /**
     * Получить публичный профиль пользователя по username (публичный endpoint).
     * Query параметр: ?includeQuests=true для включения истории квестов
     */
    #[Route('/api/users/{username}', name: 'api_users_public_profile', methods: ['GET'])]
    public function getPublicProfile(string $username, Request $request): JsonResponse
    {
        try {
            $includeQuests = $request->query->get('includeQuests') === 'true';

            if ($includeQuests) {
                $profile = $this->profileService->getPublicProfileWithQuestHistory($username);
            } else {
                $profile = $this->profileService->getPublicProfile($username);
            }

            return $this->json($profile);
        } catch (UserNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Обновить свой профиль (требует JWT аутентификации).
     */
    #[Route('/api/user/profile', name: 'api_user_profile_update', methods: ['PATCH'])]
    public function updateProfile(
        Request $request,
        #[CurrentUser] User $user
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);

            if (JSON_ERROR_NONE !== json_last_error()) {
                return $this->json(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
            }

            $updateRequest = new UpdateProfileRequest(
                email: $data['email'] ?? null
            );

            $violations = $this->validator->validate($updateRequest);
            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[$violation->getPropertyPath()] = $violation->getMessage();
                }

                return $this->json([
                    'error' => 'Validation failed',
                    'violations' => $errors,
                ], Response::HTTP_BAD_REQUEST);
            }

            $updatedUser = $this->profileService->updateProfile($user, $updateRequest);
            $profile = $this->profileService->getProfile($updatedUser);

            return $this->json([
                'message' => 'Profile updated successfully',
                'user' => $profile,
            ]);
        } catch (UserAlreadyExistsException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return $this->json(
                ['error' => 'An error occurred while updating profile'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
