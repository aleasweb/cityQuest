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
