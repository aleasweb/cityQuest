<?php

namespace App\Tests\User\Domain\Entity;

use App\User\Domain\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserEntity()
    {
        $user = new User();

        $user->setEmail('test@example.com');
        $user->setPassword('secret');
        $user->setUsername('tester');

        $this->assertNull($user->getId());
        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('secret', $user->getPassword());
        $this->assertEquals('tester', $user->getUsername());
    }
} 