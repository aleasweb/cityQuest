<?php

declare(strict_types=1);

namespace App\User\Domain\Repository;

use App\User\Domain\Entity\User;
use Symfony\Component\Uid\Uuid;

interface UserRepositoryInterface
{
    public function save(User $user): void;

    public function findById(Uuid $id): ?User;

    public function findByEmail(string $email): ?User;

    public function findByUsername(string $username): ?User;

    public function emailExists(string $email): bool;

    public function usernameExists(string $username): bool;

    public function remove(User $user): void;
}
