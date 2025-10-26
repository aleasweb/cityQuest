<?php

declare(strict_types=1);

namespace App\User\Application\Service;

use App\User\Application\DTO\UpdateProfileRequest;
use App\User\Domain\Entity\User;
use App\User\Domain\Exception\UserAlreadyExistsException;
use App\User\Domain\Exception\UserNotFoundException;
use App\User\Domain\Repository\UserRepositoryInterface;

final class ProfileService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    /**
     * Возвращает полные данные профиля пользователя (с email).
     *
     * @return array<string, mixed>
     */
    public function getProfile(User $user): array
    {
        return [
            'id' => (string) $user->getId(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Возвращает публичные данные профиля пользователя (без email).
     *
     * @return array<string, mixed>
     */
    public function getPublicProfile(string $username): array
    {
        $user = $this->userRepository->findByUsername($username);

        if ($user === null) {
            throw UserNotFoundException::withUsername($username);
        }

        return [
            'id' => (string) $user->getId(),
            'username' => $user->getUsername(),
            'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Обновляет профиль пользователя.
     */
    public function updateProfile(User $user, UpdateProfileRequest $request): User
    {
        // Если email не передан, ничего не обновляем
        if ($request->email === null) {
            return $user;
        }

        // Проверяем, изменился ли email
        if ($request->email !== $user->getEmail()) {
            // Проверяем уникальность нового email
            if ($this->userRepository->emailExists($request->email)) {
                throw UserAlreadyExistsException::withEmail($request->email);
            }

            $user->setEmail($request->email);
            $this->userRepository->save($user);
        }

        return $user;
    }
}
