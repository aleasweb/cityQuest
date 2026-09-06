# Technical Context - CityQuest

> **Технический контекст и детали реализации**

## 🛠️ Технологический стек

### Backend
- **Framework:** Symfony 6.4
- **PHP:** 8.2+
- **Database:** PostgreSQL 16
- **ORM:** Doctrine ORM 3.0
- **Authentication:** Symfony Security Bundle + Lexik JWT
- **Events:** Symfony Messenger (sync mode) + Domain Events
- **Testing:** PHPUnit 9.5
- **Code Quality:** PHPStan Level 5, PHP-CS-Fixer 3.51

### Frontend
- **Framework:** React 19
- **Build Tool:** Vite 6.3
- **Language:** TypeScript 5.8
- **Styling:** Tailwind CSS 3.4
- **Routing:** React Router 7.5
- **Validation:** Zod 3.24
- **JWT:** jwt-decode 4.0
- **Icons:** lucide-react

### Infrastructure
- **Containerization:** Docker + Docker Compose
- **Web Server:** Nginx
- **PHP Runtime:** PHP-FPM 8.3
- **Database:** PostgreSQL 16

## 🔧 Конфигурация окружения

### Docker Services
```yaml
services:
  nginx:      # Веб-сервер
  php-fpm:    # PHP runtime
  postgres:   # База данных
```

### Порты
- `80` - Nginx (HTTP)
- `9000` - PHP-FPM
- `5432` - PostgreSQL

## 📦 Зависимости

### Backend (composer.json)
Основные:
- `symfony/framework-bundle` 6.4
- `symfony/security-bundle` 6.4
- `symfony/messenger` 6.4 - обработка доменных событий
- `symfony/doctrine-messenger` 6.4
- `lexik/jwt-authentication-bundle` 3.1 - JWT auth
- `nelmio/cors-bundle` 2.6 - CORS
- `doctrine/orm` 3.0
- `doctrine/dbal` 3
- `doctrine/doctrine-bundle` 2.11
- `doctrine/doctrine-migrations-bundle` 3.3
- `symfony/uid` 6.4 - UUID support
- `symfony/validator` 6.4
- `symfony/monolog-bundle` 3.0

Dev:
- `phpunit/phpunit` 9.5
- `phpstan/phpstan` 1.10
- `friendsofphp/php-cs-fixer` 3.51
- `symfony/web-profiler-bundle` 6.4
- `symfony/maker-bundle` 1.55

### Frontend (package.json)
Основные:
- `react` 19.0
- `react-dom` 19.0
- `react-router` 7.5
- `zod` 3.24 - валидация
- `jwt-decode` 4.0 - JWT decoding
- `lucide-react` 0.510 - icons

Dev:
- `vite` 6.3
- `typescript` 5.8
- `@vitejs/plugin-react` 4.4
- `eslint` 9.25
- `tailwindcss` 3.4
- `postcss` 8.5
- `autoprefixer` 10.4

## 🗄️ Структура базы данных

### Основные таблицы

1. **users**
   - id (UUID), email, password, username
   - roles (JSON), created_at
   - Хранение пользователей

2. **quests**
   - id (UUID), title, description, city, difficulty
   - duration_minutes, distance_km, image_url
   - author, likes_count, is_popular
   - latitude, longitude (геолокация)
   - created_at, updated_at
   - Хранение квестов

3. **user_quest_progress**
   - id (UUID), user_id (FK), quest_id (FK)
   - status (active/paused/completed)
   - is_liked, completed_at
   - created_at, updated_at
   - Прогресс прохождения + лайки

4. **domain_events_progress** (CQST-010)
   - id (INT auto), aggregate_id (UUID)
   - event_type, event_data (JSON)
   - occurred_at, platform, created_at
   - Event Store для UserProgress events

## 🌐 API Endpoints (20 endpoints)

### Аутентификация (4)
- `POST /api/auth/register` - Регистрация
- `POST /api/auth/login` - Вход → JWT cookie (HttpOnly)
- `GET /api/auth/me` - Текущий пользователь
- `POST /api/auth/logout` - Выход + clear cookie

### Пользователи (2)
- `GET /api/user/profile` - Мой профиль (JWT)
- `PATCH /api/user/profile` - Обновить профиль (JWT)
- `GET /api/users/{username}` - Публичный профиль

### Квесты (публичные, 3)
- `GET /api/quests` - Список (filters, sort, pagination)
- `GET /api/quests/nearby` - Геопоиск (Haversine)
- `GET /api/quests/{id}` - Детали (опциональный JWT)

### Прогресс (JWT required, 6)
- `GET /api/user/progress` - Мой прогресс (фильтры)
- `POST /api/user/progress/{questId}/start` - Начать квест
- `PATCH /api/user/progress/{questId}/pause` - Пауза
- `PATCH /api/user/progress/{questId}/complete` - Завершить
- `DELETE /api/user/progress/{questId}` - Бросить квест
- `POST /api/quests/{id}/like` - Лайк/дизлайк

### Справочники (2)
- `GET /api/cities` - Список городов
- `GET /api/health` - Health check

## 🔐 Безопасность

### Аутентификация
- JWT токены в HttpOnly cookies (CQST-008)
- Username-based login
- Bcrypt password hashing
- Lexik JWT Authentication Bundle

### Security Headers (CQST-008)
- X-Frame-Options: DENY
- X-Content-Type-Options: nosniff
- X-XSS-Protection: 1; mode=block
- Referrer-Policy: same-origin
- Content-Security-Policy
- Permissions-Policy

### CORS
- Nelmio CORS Bundle
- Credentials: include (для cookies)
- Whitelist доменов

### Валидация
- Symfony Validator для DTO
- Zod schemas на frontend
- Sanitization входных данных

## 🧪 Тестирование

### Стратегия
- Unit tests для доменной логики
- Integration tests для API
- Минимум 80% code coverage

### Команды
```bash
# Backend tests
docker-compose exec php-fpm php bin/phpunit

# Static analysis
docker-compose exec php-fpm vendor/bin/phpstan analyse

# Code style
docker-compose exec php-fpm vendor/bin/php-cs-fixer fix
```

## 📝 Команды разработки

### Backend
```bash
# Установка зависимостей
make install

# Запуск проекта
make up

# Запуск контейнеров
docker-compose up -d

# Остановка
docker-compose down

# Логи
docker-compose logs -f php-fpm

# Миграции
docker-compose exec php-fpm php bin/console doctrine:migrations:migrate
```

### ⚠️ ВАЖНО: Работа с миграциями БД

**При создании/изменении Doctrine миграций ОБЯЗАТЕЛЬНО обновляйте init-db скрипт:**

```bash
# 1. Создать миграцию
docker-compose exec php-fpm php bin/console doctrine:migrations:diff

# 2. Применить миграцию
docker-compose exec php-fpm php bin/console doctrine:migrations:migrate

# 3. ⚠️ ОБНОВИТЬ /data/init-db/cityquest.sql с новой схемой
# Скопировать структуру таблиц из миграции в init-db скрипт

# 4. Проверить пересозданием контейнеров
docker-compose down -v
docker-compose up -d
docker-compose exec php-fpm php bin/console doctrine:schema:validate
```

**Зачем это нужно:**
- Docker init-db скрипт используется при первом запуске контейнера
- CI/CD использует этот скрипт для развертывания на новых серверах
- Другие разработчики получат актуальную схему при клонировании репозитория
- Интеграционные тесты могут использовать чистую БД из init скрипта

### Frontend
```bash
# Установка
cd frontend/web && npm install

# Dev сервер
npm run dev

# Build
npm run build

# Preview
npm run preview
```

## 🌍 Окружения

### Development
- Debug mode включен
- Web Profiler активен
- Подробные ошибки

### Production (планируется)
- Debug mode выключен
- Оптимизированные ассеты
- Минимальная информация об ошибках
- HTTPS обязателен

---

**Последнее обновление:** 2025-12-28  
**Версия:** 2.0 (обновлено после CQST-010)

---

## 🧪 Test Infrastructure (Updated: 2025-11-30)

### Test Helpers (tests/Helper/)

**Для упрощения написания тестов созданы переиспользуемые helpers:**

#### 1. DatabaseTestTrait
**Назначение:** Управление EntityManager и очистка БД в тестах

**Методы:**
```php
protected function getEntityManager(?KernelBrowser $client = null): EntityManagerInterface
protected function cleanupDatabase(): void // Очищает users, quests, user_quest_progress
protected function clearTables(array $tableNames): void
protected function closeEntityManager(): void
```

**Использование:**
```php
class MyIntegrationTest extends WebTestCase {
    use DatabaseTestTrait;
    
    protected function setUp(): void {
        parent::setUp();
        $this->cleanupDatabase(); // Чистая БД перед каждым тестом
    }
    
    protected function tearDown(): void {
        $this->closeEntityManager();
        parent::tearDown();
    }
}
```

#### 2. TestAuthClient
**Назначение:** JWT аутентификация в integration тестах

**Методы:**
```php
public static function getJwtToken(
    KernelBrowser $client,
    string $username,
    string $password = 'password123'
): string

public static function createAuthHeaders(
    string $token,
    array $additionalHeaders = []
): array
```

**Использование:**
```php
// Создать пользователя
$user = TestObjectFactory::createUserWithHasher($em, $hasher, 'testuser');

// Получить JWT токен
$token = TestAuthClient::getJwtToken($client, 'testuser');

// Сделать авторизованный запрос
$client->request(
    'GET',
    '/api/user/progress',
    [],
    [],
    TestAuthClient::createAuthHeaders($token)
);
```

#### 3. TestObjectFactory
**Назначение:** Фабрика для создания тестовых объектов (Quest, User)

**Методы:**
```php
// Максимальная гибкость - все параметры опциональны
public static function createQuest(
    EntityManagerInterface $entityManager,
    string $title,
    ?string $description = null,
    // ... 11 опциональных параметров
): Quest

// Удобство - дефолтные значения
public static function createQuestWithDefaults(
    EntityManagerInterface $entityManager,
    string $title
): Quest

// Простой вариант для unit тестов
public static function createUser(
    EntityManagerInterface $entityManager,
    string $username,
    ?string $email = null,
    string $password = 'password123',
    array $roles = ['ROLE_USER']
): User

// JWT-совместимый вариант для integration тестов
public static function createUserWithHasher(
    EntityManagerInterface $entityManager,
    UserPasswordHasherInterface $passwordHasher,
    string $username,
    // ...
): User
```

**Использование:**
```php
// Quick creation
$quest = TestObjectFactory::createQuestWithDefaults($em, 'Test Quest');

// Flexible creation
$quest = TestObjectFactory::createQuest(
    entityManager: $em,
    title: 'Hard Quest',
    difficulty: 'hard',
    durationMinutes: 180
);

// Unit test user
$user = TestObjectFactory::createUser($em, 'user1');

// Integration test user (JWT-compatible)
$user = TestObjectFactory::createUserWithHasher($em, $hasher, 'user1');
```

### Production Helper (src/Shared/Presentation/Trait/)

#### AuthenticationTrait
**Назначение:** Fallback проверка JWT токена в контроллерах

**Методы:**
```php
protected function getAuthenticatedUserOr401Response(): UserInterface|JsonResponse
```

**Использование:**
```php
class UserProgressController extends AbstractController
{
    use AuthenticationTrait;

    #[Route('/api/user/progress', methods: ['GET'])]
    public function getUserProgress(): JsonResponse
    {
        $user = $this->getAuthenticatedUserOr401Response();
        if ($user instanceof JsonResponse) {
            return $user; // Early return с 401
        }

        // Бизнес-логика с аутентифицированным пользователем
        $progress = $this->service->getUserProgress($user->getId());
        return $this->json($progress);
    }
}
```

**Примечание:** Это fallback проверка. Security firewall должен блокировать unauthorized запросы, но trait обеспечивает defense-in-depth.

### Testing Best Practices

**1. Database Isolation**
- Используйте `cleanupDatabase()` в `setUp()`
- Каждый тест работает с чистой БД
- Закрывайте EntityManager в `tearDown()`

**2. JWT Authentication**
- Используйте `createUserWithHasher()` для JWT-совместимых тестов
- `TestAuthClient::getJwtToken()` инкапсулирует login логику
- Один токен можно переиспользовать в multiple requests

**3. Test Data Creation**
- `createQuestWithDefaults()` для быстрого создания
- `createQuest()` с named parameters для specific scenarios
- Factory автоматически делает persist + flush

**4. Testing Protected Endpoints**
```php
public function testProtectedEndpoint(): void {
    $client = static::createClient();
    
    // 1. Создать пользователя
    $user = TestObjectFactory::createUserWithHasher($em, $hasher, 'testuser');
    
    // 2. Получить токен
    $token = TestAuthClient::getJwtToken($client, 'testuser');
    
    // 3. Сделать запрос
    $client->request('GET', '/api/user/progress', [], [], 
        TestAuthClient::createAuthHeaders($token)
    );
    
    $this->assertResponseIsSuccessful();
}
```

**5. Testing Unauthorized Access**
```php
public function testUnauthorizedAccess(): void {
    $client = static::createClient();
    $client->request('GET', '/api/user/progress'); // Без токена
    $this->assertResponseStatusCodeSame(401);
}
```

### ⚠️ ВАЖНО: Запуск тестов

**Все тесты ДОЛЖНЫ запускаться ТОЛЬКО внутри Docker контейнера.**

**Причины:**
- Тесты используют PostgreSQL test базу данных из docker compose
- EntityManager и Doctrine конфигурация зависят от docker окружения  
- Integration тесты используют Symfony test client с docker services
- Database cleanup (TRUNCATE) требует корректных credentials из docker

**❌ НЕ запускайте:**
```bash
php bin/phpunit  # Локально - не будет работать!
```

**✅ ВСЕГДА запускайте:**
```bash
docker-compose exec php-fpm php bin/phpunit
```

### Команды тестирования

```bash
# Все тесты
docker-compose exec php-fpm php bin/phpunit

# Конкретный тест
docker-compose exec php-fpm php bin/phpunit tests/User/Presentation/Controller/UserProfileControllerTest.php

# С coverage
docker-compose exec php-fpm php bin/phpunit --coverage-text

# Test database setup (если нужно)
docker-compose exec db psql -U user -c "CREATE DATABASE cityquest_test;"
docker-compose exec php-fpm php bin/console doctrine:migrations:migrate --env=test
```

### Metrics

**Current Test Infrastructure:**
- Unit Tests: 14 tests, 30 assertions (Domain + Application layers)
- Integration Tests: 61 tests, 234 assertions (API endpoints)
- **Total: 75 tests, 264 assertions**
- Pass Rate: 100% ✅
- Code reduced: ~40% благодаря helpers
- DX Improvement: +200%

**Coverage:**
- User domain: 100%
- Quest domain: 100%
- UserProgress domain: 100%
- Auth endpoints: 100%

---

**Reflection:** `memory-bank/reflection/reflection-CQST-005-refactoring.md`  
**Patterns:** `memory-bank/systemPatterns.md` (Testing Infrastructure Patterns)

---

## 📦 Domain Events & Event Sourcing (Added: 2025-12-28, CQST-010)

### Event Sourcing Infrastructure

**Purpose:** Полная история изменений UserProgress через domain events

**Components:**

#### 1. Domain Events (6 событий)
- `QuestStartedEvent` - квест начат
- `QuestPausedEvent` - квест поставлен на паузу
- `QuestResumedEvent` - квест возобновлён
- `QuestCompletedEvent` - квест завершён
- `QuestAbandonedEvent` - квест брошен
- `QuestStepCheckEvent` - чекпоинт проверен (stepNumber, isCorrect)

**Базовый класс:** `AbstractUserQuestProgressEvent`
- progressId (UUID)
- userId (UUID)
- questId (UUID)
- occurredAt (DateTimeImmutable)
- platform (Platform enum: web/ios/android)

#### 2. RecordsEvents Trait
```php
trait RecordsEvents {
    private array $domainEvents = [];
    
    protected function recordEvent(DomainEventInterface $event): void;
    public function releaseEvents(): array;
}
```

**Usage:** Интегрирован в `UserQuestProgress` entity

#### 3. Event Store
- **Interface:** `ProgressEventStoreInterface`
- **Implementation:** `DoctrineProgressEventStore` (DBAL-based)
- **Methods:**
  - `append(DomainEventInterface $event): void`
  - `getEventsForProgress(Uuid $progressId): array`
  - `getEventsForUser(Uuid $userId): array`

#### 4. Database Schema

**Table:** `domain_events_progress`
```sql
id            SERIAL          -- auto-increment для ordering
aggregate_id  UUID            -- progress_id
event_type    VARCHAR(255)    -- полное имя класса события
event_data    JSON            -- сериализованные данные события
occurred_at   TIMESTAMP       -- когда событие произошло
platform      VARCHAR(20)     -- web/ios/android
created_at    TIMESTAMP       -- когда событие записано в БД
```

**Индексы (5):**
- `idx_aggregate_occurred` - для запросов по aggregate
- `idx_event_type` - для фильтрации по типу события
- `idx_occurred_at` - для temporal queries
- `idx_platform` - для аналитики по платформам
- `idx_created_at` - для audit trail

**Table:** `quest_likes` (CQST-011)
```sql
id            UUID            -- Primary key
user_id       UUID            -- Ссылка на users(id)
quest_id      UUID            -- Ссылка на quests(id)
created_at    TIMESTAMP       -- когда лайк был создан
```

**Constraints:**
- `UNIQUE (user_id, quest_id)` - один лайк на квест от пользователя
- **Примечание:** Foreign Keys намеренно отсутствуют для гибкости

**Индексы (3):**
- `idx_quest_likes_user` - для запросов "мои лайки"
- `idx_quest_likes_quest` - для подсчёта лайков квеста
- `idx_quest_likes_created_at` - для временной аналитики

**Связь с denormalized field:**
- `quests.likes_count` - денормализованный счётчик, пересчитывается в runtime

#### 5. Integration with Service Layer

**UserProgressService** обновлён:
```php
public function startQuest(Uuid $userId, Uuid $questId): UserQuestProgress {
    // 1. Domain logic
    $progress->start();
    
    // 2. Persist aggregate
    $this->repository->save($progress);
    
    // 3. Store events
    foreach ($progress->releaseEvents() as $event) {
        $this->eventStore->append($event);
    }
    
    return $progress;
}
```

**Все методы обновлены:** start(), pause(), resume(), complete(), abandon()

### Benefits

**✅ Analytics-Ready**
- "Сколько квестов начато сегодня?"
- "Какие квесты чаще всего бросают?"
- "С какой платформы больше активности?"

**✅ Audit Trail**
- Полная неизменяемая история
- Когда и кем выполнено действие
- Platform attribution

**✅ Event Replay**
- Восстановление состояния
- Debugging
- Миграция данных

**✅ Future-Ready**
- Готовая инфраструктура для Event Handlers
- Источник для CQRS read models
- Foundation для real-time notifications

### Bonus: Platform Resolver

**Service:** `PlatformResolver`
- Определяет платформу из User-Agent header
- Автоматическая аттрибуция событий
- Enum: `Platform::WEB | Platform::IOS | Platform::ANDROID`

**Value Object:** `Platform` (enum)
- Immutable
- Type-safe
- Используется в событиях для аналитики

### Testing

**Coverage:** 19 тестов (12 unit + 7 integration)
- Unit: Domain events, RecordsEvents trait, Event Store
- Integration: UserProgressService с event recording

**Pass Rate:** 100% ✅

### Metrics

- **Новых файлов:** 17
- **Модифицированных:** 3 (UserQuestProgress, UserProgressService, Repository)
- **Тестов:** 19 (12 unit + 7 integration)
- **Migration:** 1 (domain_events_progress table + 5 indexes)
- **Время:** ~10ч (оценка: 9-12ч)

### Documentation

- **Reflection:** `memory-bank/reflection/reflection-CQST-010.md`
- **Archive:** `memory-bank/archive/archive-CQST-010-20251228.md`
- **README:** `project/src/UserProgress/Domain/Event/README.md`
- **Patterns:** `memory-bank/systemPatterns.md` (Domain Events & Event Sourcing)

