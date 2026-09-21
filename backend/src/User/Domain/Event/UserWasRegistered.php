<?php

declare(strict_types=1);

namespace App\User\Domain\Event;

use Symfony\Component\Uid\Uuid;

/**
 * Доменное событие, генерируемое при успешной регистрации пользователя.
 */
final class UserWasRegistered
{
    public function __construct(
        private Uuid $userId,
        private string $email,
        private string $username,
        private \DateTimeImmutable $registeredAt,
    ) {
    }

    public function getUserId(): Uuid
    {
        return $this->userId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getRegisteredAt(): \DateTimeImmutable
    {
        return $this->registeredAt;
    }
}
