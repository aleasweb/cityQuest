<?php

namespace App\Security;

use App\User\Domain\Entity\User;
use App\User\Infrastructure\Db\UserRepository;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->userRepository->findByUsername($identifier);
        if (!$user) {
            throw new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
        }
        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('Invalid user class');
        }
        return $this->userRepository->find($user->getId());
    }

    public function supportsClass(string $class): bool
    {
        return $class === User::class;
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('Invalid user class');
        }
        $user->setPassword($newHashedPassword);
        $this->userRepository->save($user);
    }
} 