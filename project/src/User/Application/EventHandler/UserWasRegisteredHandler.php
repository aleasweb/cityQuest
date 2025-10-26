<?php

declare(strict_types=1);

namespace App\User\Application\EventHandler;

use App\User\Domain\Event\UserWasRegistered;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Обработчик события UserWasRegistered.
 * 
 * По умолчанию обрабатывается СИНХРОННО.
 * Функционал будет добавлен позднее.
 */
#[AsMessageHandler]
final class UserWasRegisteredHandler
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(UserWasRegistered $event): void
    {
        // TODO: Добавить функционал обработчика
        // Возможные действия:
        // - Отправка welcome email
        // - Создание профиля пользователя
        // - Аналитика/метрики
        // - Webhook уведомления
        
        $this->logger->info('User was registered', [
            'userId' => $event->getUserId()->toRfc4122(),
            'email' => $event->getEmail(),
            'username' => $event->getUsername(),
            'registeredAt' => $event->getRegisteredAt()->format('Y-m-d H:i:s'),
        ]);
    }
}
