<?php

declare(strict_types=1);

namespace App\Tests\User\Application\EventHandler;

use App\User\Application\EventHandler\UserWasRegisteredHandler;
use App\User\Domain\Event\UserWasRegistered;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Тест обработчика события UserWasRegistered.
 */
final class UserWasRegisteredHandlerTest extends TestCase
{
    public function testHandlerLogsUserRegistration(): void
    {
        // Arrange
        $userId = Uuid::v4();
        $email = 'test@example.com';
        $username = 'testuser';
        $registeredAt = new \DateTimeImmutable();

        $event = new UserWasRegistered(
            userId: $userId,
            email: $email,
            username: $username,
            registeredAt: $registeredAt
        );

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('info')
            ->with(
                'User was registered',
                $this->callback(function (array $context) use ($userId, $email, $username) {
                    return $context['userId'] === $userId->toRfc4122()
                        && $context['email'] === $email
                        && $context['username'] === $username
                        && isset($context['registeredAt']);
                })
            );

        $handler = new UserWasRegisteredHandler($logger);

        // Act
        $handler($event);

        // Assert - проверка выполнена через mock
    }

    public function testEventContainsCorrectData(): void
    {
        // Arrange
        $userId = Uuid::v4();
        $email = 'test@example.com';
        $username = 'testuser';
        $registeredAt = new \DateTimeImmutable();

        // Act
        $event = new UserWasRegistered(
            userId: $userId,
            email: $email,
            username: $username,
            registeredAt: $registeredAt
        );

        // Assert
        $this->assertSame($userId, $event->getUserId());
        $this->assertSame($email, $event->getEmail());
        $this->assertSame($username, $event->getUsername());
        $this->assertSame($registeredAt, $event->getRegisteredAt());
    }
}
