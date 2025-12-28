# Style Guide - CityQuest

> **Руководство по стилю кода и UI/UX**

## 🎨 UI/UX Дизайн

### Цветовая схема

#### Дневная тема
- **Основной фон:** `#ffffff`
- **Акцентный цвет:** `#ed8e34` (оранжевый)
- **Текст:** `#1f2937` (темно-серый)
- **Вторичный текст:** `#6b7280` (серый)

#### Ночная тема
- **Основной фон:** `#1a1a1a` (темный)
- **Акцентный цвет:** `#3b1f16` (темно-оранжевый)
- **Текст:** `#e5e7eb` (светло-серый)
- **Вторичный текст:** `#9ca3af` (серый)

### Tailwind конфигурация
```javascript
theme: {
  colors: {
    primary: '#ed8e34',
    primaryDark: '#3b1f16',
    background: {
      light: '#ffffff',
      dark: '#1a1a1a'
    },
    text: {
      light: '#1f2937',
      dark: '#e5e7eb'
    }
  }
}
```

### Компоненты

#### Кнопки
- **Primary:** Оранжевый фон, белый текст
- **Secondary:** Прозрачный фон, оранжевая граница
- **Размеры:** sm, md, lg
- **Состояния:** default, hover, active, disabled

#### Карточки квестов
- Изображение сверху (16:9)
- Название квеста (h3)
- Краткое описание (2 строки max)
- Метаданные (город, сложность, время)
- Лайки и кнопка действия

#### Формы
- Label над полем
- Placeholder для подсказок
- Валидация с красной подсветкой
- Success state с зеленой галочкой

### Типографика
- **Заголовки:** Inter, sans-serif
- **Основной текст:** Inter, sans-serif
- **H1:** 32px, bold
- **H2:** 24px, semi-bold
- **H3:** 20px, semi-bold
- **Body:** 16px, regular
- **Small:** 14px, regular

### Отступы и сетка
- **Базовый модуль:** 8px
- **Контейнер:** max-width 1200px
- **Отступы:** кратные 8px (8, 16, 24, 32, 48)
- **Grid:** 12 колонок на desktop, 4 на mobile

## 💻 Code Style

### PHP (Backend)

#### Стандарт: PSR-12
```php
<?php

declare(strict_types=1);

namespace App\User\Domain\Entity;

final class User
{
    public function __construct(
        private string $id,
        private string $email,
        private string $username
    ) {
    }
    
    public function getId(): string
    {
        return $this->id;
    }
}
```

#### Правила
- Strict types всегда включен (`declare(strict_types=1)`)
- Final классы по умолчанию (кроме abstract)
- Readonly properties где возможно (особенно в events)
- Constructor property promotion
- Typed properties и return types обязательны
- Named arguments для создания objects с >3 параметрами
- Camel case для методов и свойств
- Pascal case для классов и enums
- Events наследуют абстрактный базовый класс
- RecordsEvents trait для entities с событиями

#### Domain Events (CQST-010)
```php
// Базовый класс события
abstract class AbstractUserQuestProgressEvent implements DomainEventInterface {
    public function __construct(
        private readonly Uuid $progressId,
        private readonly Uuid $userId,
        private readonly Uuid $questId,
        private readonly \DateTimeImmutable $occurredAt,
        private readonly ?Platform $platform = null
    ) {}
    
    public function getProgressId(): Uuid { return $this->progressId; }
    // ... getters
}

// Конкретное событие
final class QuestStartedEvent extends AbstractUserQuestProgressEvent {}

// Entity с событиями
class UserQuestProgress {
    use RecordsEvents;
    
    public function start(): void {
        $this->status = QuestStatus::Active;
        $this->recordEvent(new QuestStartedEvent(
            $this->id, $this->user->getId(), $this->quest->getId(),
            new \DateTimeImmutable()
        ));
    }
}
```

#### Комментарии
```php
/**
 * Retrieves user by email address.
 *
 * @param string $email User's email address
 * @return User|null User entity or null if not found
 */
public function findByEmail(string $email): ?User
{
    // Implementation
}
```

### TypeScript (Frontend)

#### Стиль
```typescript
// Types + Zod schemas для валидации
import { z } from 'zod';

const QuestSchema = z.object({
  id: z.string().uuid(),
  title: z.string(),
  description: z.string().nullable(),
  city: z.string().nullable(),
  difficulty: z.enum(['easy', 'medium', 'hard']).nullable(),
  // ...
});

type Quest = z.infer<typeof QuestSchema>;

// React компонент
interface QuestCardProps {
  quest: Quest;
  onLike?: () => void;
}

export function QuestCard({ quest, onLike }: QuestCardProps) {
  return (
    <div className="bg-white rounded-lg shadow-md p-4">
      <h3 className="text-xl font-semibold">{quest.title}</h3>
      <p className="text-gray-600">{quest.description}</p>
    </div>
  );
}
```

#### Правила
- Strict mode enabled
- Zod schemas для валидации API responses
- `z.infer<typeof Schema>` для типов
- Named exports (не default)
- Function declarations для компонентов
- Interface для props
- PascalCase для компонентов и types
- camelCase для функций и переменных
- UPPER_SNAKE_CASE для констант

### Именование

#### Backend (PHP)
- **Controllers:** `AuthController`, `QuestController`, `UserProgressController`
- **Services:** `AuthenticationService`, `QuestService`, `UserProgressService`
- **Entities:** `User`, `Quest`, `UserQuestProgress`
- **Repositories:** `DoctrineUserRepository`, `DoctrineQuestRepository`
- **DTOs:** `RegisterUserRequest`, `UpdateProfileRequest`
- **Events:** `UserWasRegistered`, `QuestStartedEvent`
- **ValueObjects:** `QuestStatus`, `Platform`

#### Frontend (TypeScript)
- **Components:** `QuestCard`, `Toast`, `ActiveQuestModal`
- **Pages:** `HomePage`, `QuestDetail`, `UserProfile`
- **Context:** `AuthContext`
- **Utils:** `api`, `cacheManager`
- **Types:** `Quest`, `User`, `UserProgress`, `City`
- **Schemas:** `QuestSchema`, `UserSchema` (Zod)

### Структура файлов

#### Backend
```
User/
├── Domain/
│   ├── Entity/
│   │   └── User.php
│   ├── Repository/
│   │   └── UserRepositoryInterface.php
│   └── Service/
│       └── UserDomainService.php
├── Application/
│   ├── DTO/
│   │   ├── CreateUserRequest.php
│   │   └── UserResponse.php
│   └── Service/
│       └── UserApplicationService.php
├── Infrastructure/
│   └── Db/
│       └── DoctrineUserRepository.php
└── Presentation/
    └── Controller/
        └── UserController.php
```

#### Frontend (актуальная структура)
```
src/
├── react-app/
│   ├── components/
│   │   ├── Toast.tsx              # Notifications (success/error)
│   │   ├── ActiveQuestModal.tsx   # 409 Conflict modal
│   │   └── QuestCard.tsx          # Reusable quest card
│   ├── pages/
│   │   ├── HomePage.tsx           # Quest list + filters
│   │   ├── QuestDetail.tsx        # Quest details + actions
│   │   └── UserProfile.tsx        # User progress history
│   ├── context/
│   │   └── AuthContext.tsx        # JWT auth state
│   └── routes.tsx                 # React Router config
├── shared/
│   ├── api.ts                     # HTTP client (JWT cookies)
│   ├── cacheManager.ts            # LocalStorage cache
│   └── types.ts                   # TypeScript types + Zod
└── index.css                      # Tailwind styles
```

## 📝 Документация

### Код
- PHPDoc для всех public методов
- JSDoc для сложных функций
- Inline комментарии для неочевидной логики
- README.md в каждом модуле

### API
- OpenAPI 3.0 спецификация
- Примеры request/response
- Описание всех ошибок

---

**Последнее обновление:** 2025-12-28  
**Версия:** 1.1
