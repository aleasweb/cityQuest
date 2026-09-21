<?php

declare(strict_types=1);

namespace App\User\Application\Service;

use App\User\Application\DTO\RegisterUserRequest;
use App\User\Domain\Entity\User;
use App\User\Domain\Event\UserWasRegistered;
use App\User\Domain\Exception\UserAlreadyExistsException;
use App\User\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AuthenticationService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function register(RegisterUserRequest $request): User
    {
        if ($this->userRepository->emailExists($request->email)) {
            throw UserAlreadyExistsException::withEmail($request->email);
        }

        if ($this->userRepository->usernameExists($request->username)) {
            throw UserAlreadyExistsException::withUsername($request->username);
        }

        $user = new User();
        $user->setEmail($request->email);
        $user->setUsername($request->username);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $request->password);
        $user->setPassword($hashedPassword);

        $this->userRepository->save($user);

        // Отправка доменного события (по умолчанию обрабатывается синхронно)
        $this->messageBus->dispatch(new UserWasRegistered(
            userId: $user->getId(),
            email: $user->getEmail(),
            username: $user->getUsername(),
            registeredAt: $user->getCreatedAt(),
        ));

        return $user;
    }
}
