<?php

declare(strict_types=1);

namespace App\Tests\User\Domain\Entity;

use App\User\Domain\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class UserTest extends TestCase
{
    public function testUserCanBeCreated(): void
    {
        $user = new User();
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(Uuid::class, $user->getId());
        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getCreatedAt());
    }

    public function testUserIdIsUuid(): void
    {
        $user = new User();
        
        $this->assertInstanceOf(Uuid::class, $user->getId());
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $user->getId()->toRfc4122()
        );
    }

    public function testUserCanSetAndGetEmail(): void
    {
        $user = new User();
        $email = 'test@example.com';
        
        $user->setEmail($email);
        
        $this->assertSame($email, $user->getEmail());
    }

    public function testUserCanSetAndGetUsername(): void
    {
        $user = new User();
        $username = 'testuser';
        
        $user->setUsername($username);
        
        $this->assertSame($username, $user->getUsername());
    }

    public function testUserCanSetAndGetPassword(): void
    {
        $user = new User();
        $password = 'hashedPassword123';
        
        $user->setPassword($password);
        
        $this->assertSame($password, $user->getPassword());
    }

    public function testGetUserIdentifierReturnsEmail(): void
    {
        $user = new User();
        $email = 'user@example.com';
        
        $user->setEmail($email);
        
        $this->assertSame($email, $user->getUserIdentifier());
    }

    public function testUserHasDefaultRoleUser(): void
    {
        $user = new User();
        
        $roles = $user->getRoles();
        
        $this->assertContains('ROLE_USER', $roles);
        $this->assertCount(1, array_unique($roles));
    }

    public function testUserCanHaveMultipleRoles(): void
    {
        $user = new User();
        
        $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $roles = $user->getRoles();
        
        $this->assertContains('ROLE_USER', $roles);
        $this->assertContains('ROLE_ADMIN', $roles);
    }

    public function testAddRoleAddsNewRole(): void
    {
        $user = new User();
        
        $user->addRole('ROLE_ADMIN');
        $roles = $user->getRoles();
        
        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertContains('ROLE_USER', $roles);
    }

    public function testAddRoleDoesNotDuplicateRole(): void
    {
        $user = new User();
        
        $user->addRole('ROLE_ADMIN');
        $user->addRole('ROLE_ADMIN');
        
        $roles = $user->getRoles();
        $adminRoles = array_filter($roles, fn($role) => $role === 'ROLE_ADMIN');
        
        $this->assertCount(1, $adminRoles);
    }

    public function testCreatedAtIsSetOnConstruction(): void
    {
        $beforeCreation = new \DateTimeImmutable();
        
        $user = new User();
        
        $afterCreation = new \DateTimeImmutable();
        
        $this->assertGreaterThanOrEqual($beforeCreation, $user->getCreatedAt());
        $this->assertLessThanOrEqual($afterCreation, $user->getCreatedAt());
    }

    public function testEraseCredentialsDoesNothing(): void
    {
        $user = new User();
        $password = 'hashedPassword';
        
        $user->setPassword($password);
        $user->eraseCredentials();
        
        // Password should remain unchanged as we don't store plainPassword
        $this->assertSame($password, $user->getPassword());
    }
} 