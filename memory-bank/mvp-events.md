# События (Domain Events) - CityQuest

## 📋 Обзор

Проект использует Symfony Messenger для обработки доменных событий. По умолчанию события обрабатываются **синхронно**, но легко переключаются на асинхронную обработку при необходимости.

## 🎯 Доступные события

### UserWasRegistered

**Класс:** `App\User\Domain\Event\UserWasRegistered`  
**Обработчик:** `App\User\Application\EventHandler\UserWasRegisteredHandler`  
**Режим обработки:** Синхронный (по умолчанию)

## 🔄 Как переключить на асинхронную обработку

### Шаг 1: Настроить транспорт

Отредактируйте `config/packages/messenger.yaml`:

```yaml
framework:
    messenger:
        failure_transport: failed

        transports:
            async:
                dsn: 'doctrine://default?auto_setup=0'
                options:
                    use_notify: true
                    check_delayed_interval: 60000
                retry_strategy:
                    max_retries: 3
                    multiplier: 2
            failed: 'doctrine://default?queue_name=failed&auto_setup=0'

        routing:
            # Маршрутизация события на асинхронную обработку
            'App\User\Domain\Event\UserWasRegistered': async
```

### Шаг 2: Создать таблицы для очереди

```bash
docker-compose exec php-fpm php bin/console messenger:setup-transports
```

### Шаг 3: Запустить Worker

```bash
# Development - с подробным логированием
docker-compose exec php-fpm php bin/console messenger:consume async -vv

# Production - с автоперезапуском каждый час
docker-compose exec php-fpm php bin/console messenger:consume async --time-limit=3600
```

### Шаг 4 (Production): Настроить Supervisor

Создайте `/etc/supervisor/conf.d/messenger-worker.conf`:

```ini
[program:messenger-consume]
command=php /app/bin/console messenger:consume async --time-limit=3600
user=www-data
numprocs=2
startsecs=0
autostart=true
autorestart=true
process_name=%(program_name)s_%(process_num)02d
```

## 📊 Мониторинг (только для асинхронной обработки)

### Проверка очередей

```bash
# Статистика очередей
docker-compose exec php-fpm php bin/console messenger:stats

# Список всех обработчиков
docker-compose exec php-fpm php bin/console debug:messenger
```

### Проверка БД

```sql
-- Количество сообщений в очереди
SELECT COUNT(*) FROM messenger_messages WHERE queue_name = 'default';

-- Последние сообщения
SELECT * FROM messenger_messages ORDER BY created_at DESC LIMIT 10;

-- Failed сообщения
SELECT * FROM messenger_messages WHERE queue_name = 'failed';
```

## 📝 Добавление нового события

### 1. Создайте класс события

`src/User/Domain/Event/UserPasswordWasReset.php`:
```php
<?php

declare(strict_types=1);

namespace App\User\Domain\Event;

use Symfony\Component\Uid\Uuid;

final class UserPasswordWasReset
{
    public function __construct(
        private Uuid $userId,
        private \DateTimeImmutable $resetAt,
    ) {}
    
    public function getUserId(): Uuid
    {
        return $this->userId;
    }
    
    public function getResetAt(): \DateTimeImmutable
    {
        return $this->resetAt;
    }
}
```

### 2. Создайте обработчик

`src/User/Application/EventHandler/UserPasswordWasResetHandler.php`:
```php
<?php

declare(strict_types=1);

namespace App\User\Application\EventHandler;

use App\User\Domain\Event\UserPasswordWasReset;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UserPasswordWasResetHandler
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}
    
    public function __invoke(UserPasswordWasReset $event): void
    {
        // Обработка события
        $this->logger->info('Password was reset', [
            'userId' => $event->getUserId()->toRfc4122(),
        ]);
    }
}
```

### 3. Отправьте событие

В вашем Application Service:
```php
$this->messageBus->dispatch(new UserPasswordWasReset(
    userId: $user->getId(),
    resetAt: new \DateTimeImmutable(),
));
```

### 4. (Опционально) Настройте маршрутизацию

Если хотите асинхронную обработку, добавьте в `messenger.yaml`:
```yaml
routing:
    'App\User\Domain\Event\UserPasswordWasReset': async
```

## 🧪 Тестирование событий

### Unit тест события

```php
public function testEventContainsCorrectData(): void
{
    $userId = Uuid::v4();
    $event = new UserWasRegistered($userId, 'test@example.com', 'user', new \DateTimeImmutable());
    
    $this->assertSame($userId, $event->getUserId());
}
```

### Unit тест обработчика

```php
public function testHandlerProcessesEvent(): void
{
    $logger = $this->createMock(LoggerInterface::class);
    $logger->expects($this->once())->method('info');
    
    $handler = new UserWasRegisteredHandler($logger);
    $event = new UserWasRegistered(/* ... */);
    
    $handler($event);
}
```

### Integration тест (mock MessageBus)

```php
public function testServiceDispatchesEvent(): void
{
    $messageBus = $this->createMock(MessageBusInterface::class);
    $messageBus->expects($this->once())
        ->method('dispatch')
        ->with($this->isInstanceOf(UserWasRegistered::class));
    
    $service = new AuthenticationService($repo, $hasher, $messageBus);
    $service->register($request);
}
```

## ⚠️ Лучшие практики

1. **События должны содержать все необходимые данные** (Event Carried State Transfer)
   - ✅ Хорошо: `new UserWasRegistered($userId, $email, $username, $timestamp)`
   - ❌ Плохо: `new UserWasRegistered($userId)` (потребуется дополнительный запрос в БД)

2. **Обработчики должны быть идемпотентными**
   - Повторная обработка не должна вызывать проблем

3. **Используйте явные названия событий в прошедшем времени**
   - ✅ `UserWasRegistered`, `OrderWasPlaced`
   - ❌ `RegisterUser`, `PlaceOrder`

4. **Одно событие - одна ответственность**
   - Лучше несколько маленьких событий, чем одно большое

5. **В production используйте supervisor/systemd для worker'ов**
   - Обеспечивает автоматический перезапуск

---

**Создано:** 2025-10-26  
**Статус:** Синхронная обработка активна
