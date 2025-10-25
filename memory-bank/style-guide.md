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
- Strict types всегда включен
- Final классы по умолчанию
- Private properties с readonly где возможно
- Constructor property promotion
- Typed properties и return types обязательны
- Camel case для методов и свойств
- Pascal case для классов

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
// Interfaces для типов
interface QuestCardProps {
  id: string;
  title: string;
  description: string;
  city: string;
  difficulty: 'easy' | 'medium' | 'hard';
}

// React компонент
export const QuestCard: React.FC<QuestCardProps> = ({
  id,
  title,
  description,
  city,
  difficulty
}) => {
  return (
    <div className="bg-white rounded-lg shadow-md p-4">
      <h3 className="text-xl font-semibold">{title}</h3>
      <p className="text-gray-600">{description}</p>
    </div>
  );
};
```

#### Правила
- Strict mode enabled
- Explicit types для функций и переменных
- PascalCase для компонентов
- camelCase для функций и переменных
- UPPER_SNAKE_CASE для констант
- Functional components предпочтительнее классовых
- Destructuring для props

### Git Commit Messages

#### Формат
```
<type>(<scope>): <subject>

<body>

<footer>
```

#### Types
- `feat`: Новая функциональность
- `fix`: Исправление бага
- `docs`: Документация
- `style`: Форматирование кода
- `refactor`: Рефакторинг
- `test`: Тесты
- `chore`: Рутинные задачи

#### Примеры
```
feat(auth): add user registration endpoint

Implemented POST /api/auth/register with email validation
and password hashing using Symfony Security Bundle.

Closes #123
```

```
fix(quest): correct distance calculation

Fixed bug in geolocation distance calculation that was
causing incorrect results for quests near meridian.
```

### Именование

#### Backend (PHP)
- **Controllers:** `UserController`, `QuestController`
- **Services:** `UserRegistrationService`, `QuestSearchService`
- **Entities:** `User`, `Quest`, `QuestStep`
- **Repositories:** `UserRepository`, `QuestRepository`
- **DTOs:** `CreateUserRequest`, `QuestResponse`

#### Frontend (TypeScript)
- **Components:** `QuestCard`, `UserProfile`, `MapView`
- **Hooks:** `useQuests`, `useAuth`, `useGeolocation`
- **Stores:** `authStore`, `questsStore`
- **Utils:** `formatDate`, `calculateDistance`
- **Types:** `Quest`, `User`, `QuestStep`

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

#### Frontend
```
components/
├── QuestCard/
│   ├── QuestCard.tsx
│   ├── QuestCard.test.tsx
│   └── index.ts
├── UserProfile/
│   ├── UserProfile.tsx
│   ├── UserProfile.test.tsx
│   └── index.ts
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

**Последнее обновление:** 2025-10-25
