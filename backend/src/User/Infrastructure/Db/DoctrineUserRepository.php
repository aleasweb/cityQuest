<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Db;

use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

final class DoctrineUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function findById(Uuid $id): ?User
    {
        return $this->entityManager->find(User::class, $id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);
    }

    public function findByUsername(string $username): ?User
    {
        return $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => $username]);
    }

    public function emailExists(string $email): bool
    {
        return null !== $this->findByEmail($email);
    }

    public function usernameExists(string $username): bool
    {
        return null !== $this->findByUsername($username);
    }

    public function remove(User $user): void
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }
}
