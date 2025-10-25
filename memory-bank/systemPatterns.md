# System Patterns - CityQuest

> **Технические паттерны и архитектурные решения**

## 🏗️ Архитектурные паттерны

### Backend Architecture (DDD + CQRS)

#### Структура доменов
```
src/
├── User/                      # Домен пользователей
│   ├── Domain/               # Бизнес-логика
│   │   ├── Entity/          # Сущности
│   │   ├── Repository/      # Интерфейсы репозиториев
│   │   └── Service/         # Доменные сервисы
│   ├── Application/         # Уровень приложения
│   │   ├── DTO/            # Data Transfer Objects
│   │   └── Service/        # Сервисы приложения
│   ├── Infrastructure/      # Технические детали
│   │   ├── Db/             # Реализация для БД
│   │   └── Cache/          # Реализация для кеша
│   └── Presentation/        # Интерфейсы
│       ├── Controller/     # HTTP контроллеры
│       ├── Cli/            # CLI команды
│       └── View/           # Представления
├── Quest/                    # Домен квестов
└── HealthCheck/             # Проверка здоровья системы
```

### Принципы разделения

1. **Domain Layer** - Чистая бизнес-логика, независимая от фреймворка
2. **Application Layer** - Оркестрация, координация бизнес-процессов
3. **Infrastructure Layer** - Технические детали (БД, кеш, внешние API)
4. **Presentation Layer** - Точки входа (HTTP, CLI, WebSocket)

## 🔄 Используемые паттерны

### Repository Pattern
- Абстракция для работы с хранилищами данных
- Интерфейсы в Domain, реализация в Infrastructure

### DTO (Data Transfer Objects)
- Передача данных между слоями
- Валидация входных данных

### Service Layer
- Доменные сервисы: комплексная бизнес-логика
- Сервисы приложения: координация работы нескольких доменов

## 🎨 Frontend Patterns

### Component Structure
```
src/
├── react-app/
│   ├── components/         # Переиспользуемые компоненты
│   ├── pages/             # Страницы
│   ├── hooks/             # Custom hooks
│   ├── store/             # Zustand stores
│   └── utils/             # Утилиты
├── shared/                # Общий код
└── worker/                # Web workers
```

### State Management (Zustand)
- Простое и легкое управление состоянием
- Минимальный boilerplate
- TypeScript support

## 🗄️ Паттерны работы с данными

### API Communication
- REST API для всех операций
- JSON формат
- JWT для аутентификации

### Caching Strategy
- На уровне infrastructure для часто запрашиваемых данных
- Client-side кеширование в Zustand

## 🔒 Security Patterns

- HTTPS для всех запросов
- Password hashing (Symfony Security Bundle)
- JWT tokens для API
- CORS настройки для frontend

## 📏 Code Style

### PHP (Backend)
- PSR-12 coding standard
- PHP-CS-Fixer для автоформатирования
- PHPStan для статического анализа (level 8)

### TypeScript (Frontend)
- ESLint для линтинга
- Prettier для форматирования
- Strict mode enabled

## 🧪 Testing Strategy

### Backend
- Unit tests для Domain layer
- Integration tests для API endpoints
- PHPUnit framework

### Frontend
- Component tests
- E2E tests (планируется)

---

**Последнее обновление:** 2025-10-25

---

## 🔐 Authentication Patterns (Added: 2025-10-25, Task: CQST-001)

### JWT Authentication with LexikJWTAuthenticationBundle

**Pattern:** Token-based REST API authentication using JWT

**Implementation:**
```yaml
# config/packages/security.yaml
firewalls:
    api_login:
        pattern: ^/api/auth/login
        stateless: true
        json_login:
            check_path: api_auth_login
            username_path: email
            password_path: password
            success_handler: lexik_jwt_authentication.handler.authentication_success
    
    api:
        pattern: ^/api
        stateless: true
        jwt: ~
```

**Key Configuration:**
```yaml
# config/packages/lexik_jwt_authentication.yaml
lexik_jwt_authentication:
    secret_key: '%env(resolve:JWT_SECRET_KEY)%'
    public_key: '%env(resolve:JWT_PUBLIC_KEY)%'
    pass_phrase: '%env(JWT_PASSPHRASE)%'
    user_id_claim: email  # ⚠️ CRITICAL: Must match UserInterface::getUserIdentifier()
```

**Lessons:**
- `user_id_claim` must match what `getUserIdentifier()` returns
- Use email as identifier for better UX (users remember emails)
- Stateless firewalls for REST APIs
- Separate login firewall from protected API firewall

---

## 🏗️ Domain-Driven Design (DDD) Structure

### Pattern: Bounded Context Organization

**Structure:**
```
src/[BoundedContext]/
├── Domain/
│   ├── Entity/          # Core business entities
│   ├── Repository/      # Repository interfaces (contracts)
│   └── Exception/       # Domain-specific exceptions
├── Application/
│   ├── DTO/            # Data Transfer Objects with validation
│   └── Service/        # Application/Use Case services
├── Infrastructure/
│   └── Db/             # Repository implementations (Doctrine)
└── Presentation/
    └── Controller/     # API Controllers (REST)
```

**Example - User Bounded Context:**
```php
// Domain Layer
interface UserRepositoryInterface {
    public function save(User $user): void;
    public function findByEmail(string $email): ?User;
}

// Application Layer
final class AuthenticationService {
    public function __construct(
        private UserRepositoryInterface $repository,
        private UserPasswordHasherInterface $hasher,
    ) {}
    
    public function register(RegisterUserRequest $dto): User {
        // Business logic here
    }
}

// Infrastructure Layer
final class DoctrineUserRepository implements UserRepositoryInterface {
    public function __construct(
        private EntityManagerInterface $em,
    ) {}
}
```

**Benefits:**
- Clear separation of concerns
- Testable (easy to mock interfaces)
- Technology-agnostic domain layer
- Maintainable and scalable

---

## 🆔 UUID as Primary Key Pattern

### Pattern: UUID instead of auto-increment integers

**Implementation:**
```php
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
class User implements UserInterface {
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;
    
    public function __construct() {
        $this->id = Uuid::v4();
        $this->createdAt = new \DateTimeImmutable();
    }
}
```

**Migration:**
```php
public function up(Schema $schema): void {
    $this->addSql('CREATE TABLE users (
        id UUID NOT NULL,
        email VARCHAR(255) NOT NULL,
        PRIMARY KEY(id)
    )');
    $this->addSql("COMMENT ON COLUMN users.id IS '(DC2Type:uuid)'");
}
```

**Benefits:**
- No sequential IDs exposed in URLs
- Globally unique across databases
- Better for distributed systems
- Enhanced security

**Trade-offs:**
- Slightly larger storage (16 bytes vs 4-8 bytes)
- Not human-readable
- No natural ordering

---

## ✅ DTO Validation Pattern

### Pattern: Request DTOs with PHP 8 Attributes

**Implementation:**
```php
use Symfony\Component\Validator\Constraints as Assert;

final class RegisterUserRequest {
    #[Assert\NotBlank(message: 'Email is required')]
    #[Assert\Email(message: 'Invalid email format')]
    #[Assert\Length(max: 255)]
    public string $email;
    
    #[Assert\NotBlank]
    #[Assert\Length(min: 8, max: 255)]
    public string $password;
    
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 50)]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9_]+$/',
        message: 'Username can only contain letters, numbers and underscores'
    )]
    public string $username;
}
```

**Controller Usage:**
```php
public function register(Request $request): JsonResponse {
    $data = json_decode($request->getContent(), true);
    $dto = new RegisterUserRequest(...$data);
    
    $violations = $this->validator->validate($dto);
    if (count($violations) > 0) {
        $errors = [];
        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()] = $violation->getMessage();
        }
        return $this->json(['violations' => $errors], 400);
    }
    
    // Process valid DTO
}
```

**Benefits:**
- Centralized validation logic
- Type-safe
- Self-documenting (constraints visible in code)
- Easy to test

---

## 🧪 Integration Testing Pattern

### Pattern: REST API Integration Tests

**Setup:**
```php
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthControllerTest extends WebTestCase {
    public function testSuccessfulRegistration(): void {
        $client = static::createClient();
        
        $client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'user@example.com',
            'password' => 'password123',
            'username' => 'testuser',
        ]));
        
        $this->assertResponseStatusCodeSame(201);
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('user', $data);
        $this->assertEquals('user@example.com', $data['user']['email']);
    }
}
```

**Test Database:**
```bash
# Create test database
docker-compose exec db psql -U user -c "CREATE DATABASE cityquest_test;"

# Run migrations
docker-compose exec php-fpm php bin/console doctrine:migrations:migrate --env=test

# Clean before tests
docker-compose exec db psql -U user -d cityquest_test -c "TRUNCATE TABLE users CASCADE;"
```

**Best Practices:**
- Separate test database
- Clean state between tests
- Test both happy paths and error cases
- Verify response structure and status codes

---

## 🔒 Domain Exception Pattern

### Pattern: Specific domain exceptions for business rule violations

**Implementation:**
```php
namespace App\User\Domain\Exception;

class UserAlreadyExistsException extends \DomainException {
    public static function withEmail(string $email): self {
        return new self(sprintf('User with email "%s" already exists', $email));
    }
    
    public static function withUsername(string $username): self {
        return new self(sprintf('User with username "%s" already exists', $username));
    }
}
```

**Usage in Service:**
```php
public function register(RegisterUserRequest $request): User {
    if ($this->userRepository->emailExists($request->email)) {
        throw UserAlreadyExistsException::withEmail($request->email);
    }
    // ...
}
```

**Controller Error Handling:**
```php
try {
    $user = $this->service->register($dto);
    return $this->json(['user' => $user], 201);
} catch (UserAlreadyExistsException $e) {
    return $this->json(['error' => $e->getMessage()], 409);
}
```

**Benefits:**
- Clear business rule violations
- Type-safe error handling
- Semantic HTTP status codes
- Better error messages for API clients

---

**Pattern Source:** Task CQST-001 - Registration and Authentication System  
**Documentation:** `memory-bank/reflection/reflection-CQST-001.md`
