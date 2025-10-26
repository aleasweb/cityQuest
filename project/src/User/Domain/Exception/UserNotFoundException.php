<?php

declare(strict_types=1);

namespace App\User\Domain\Exception;

use Symfony\Component\Uid\Uuid;

class UserNotFoundException extends \DomainException
{
    public static function withId(Uuid $id): self
    {
        return new self(sprintf('User with ID "%s" not found', $id->toRfc4122()));
    }

    public static function withEmail(string $email): self
    {
        return new self(sprintf('User with email "%s" not found', $email));
    }

    public static function withUsername(string $username): self
    {
        return new self(sprintf('User with username "%s" not found', $username));
    }
}
