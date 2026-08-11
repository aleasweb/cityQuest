# Справка по авторизации - CityQuest

> Краткое руководство по реализации аутентификации

## 🔐 Схема авторизации

**Метод:** JWT (JSON Web Token)  
**Тип входа:** Username + Password (НЕ email)

## 📝 API Endpoints

### Регистрация
```http
POST /api/auth/register
Content-Type: application/json

{
  "email": "user@example.com",
  "username": "myusername",
  "password": "password123"
}
```

**Response (201):**
```json
{
  "message": "User registered successfully",
  "user": {
    "id": "uuid",
    "email": "user@example.com",
    "username": "myusername",
    "createdAt": "2025-12-06 10:00:00"
  }
}
```

### Вход (Login)
```http
POST /api/auth/login
Content-Type: application/json

{
  "username": "myusername",
  "password": "password123"
}
```

**Response (200):**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

### Выход (Logout)
```http
POST /api/auth/logout
Authorization: Bearer <token>
```

## 🎯 Ключевые моменты

### Backend (Symfony)
- **User Provider:** Ищет пользователя по `username` (не по email)
- **JWT Claim:** Использует `username` как user identifier
- **Password:** Bcrypt хеширование через Symfony Security

**Конфигурация (`security.yaml`):**
```yaml
providers:
    app_user_provider:
        entity:
            class: App\User\Domain\Entity\User
            property: username  # Поиск по username

api_login:
    json_login:
        username_path: username  # Принимает username
        password_path: password
```

### Frontend (React)
- **Login Form:** Поле "Login" (username), НЕ email
- **LoginData type:** `{ username: string, password: string }`
- **Token Storage:** localStorage (`jwt_token`)
- **Auto-login:** После регистрации использует `username`

**Типы (`types.ts`):**
```typescript
export interface LoginData {
  username: string;  // НЕ email!
  password: string;
}
```

## 🔒 Защищенные запросы

Для защищенных endpoint добавляйте JWT в заголовок:

```http
GET /api/user/progress
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

**Frontend автоматически добавляет токен:**
```typescript
const token = localStorage.getItem('jwt_token');
headers: {
  'Authorization': `Bearer ${token}`
}
```

## 🔓 Опциональная авторизация

**Некоторые endpoints поддерживают опциональную JWT авторизацию** - они доступны как для авторизованных, так и для неавторизованных пользователей, но возвращают разные данные в зависимости от наличия токена.

### Пример: GET /api/quests/{id}

**Без токена:**
```json
{
  "data": {
    "id": "...",
    "title": "Квест",
    "likesCount": 5,
    "isLikedByCurrentUser": false  // всегда false
  }
}
```

**С токеном:**
```json
{
  "data": {
    "id": "...",
    "title": "Квест",
    "likesCount": 5,
    "isLikedByCurrentUser": true  // реальный статус для пользователя
  }
}
```

**Реализация (Backend):**
```yaml
# security.yaml
api_quests_public:
    pattern: ^/api/quests
    methods: [GET]
    stateless: true
    provider: app_user_provider
    jwt: ~  # JWT проверяется, если присутствует

access_control:
    - { path: ^/api/quests, methods: [GET], roles: PUBLIC_ACCESS }
```

**Реализация (Controller):**
```php
$securityUser = $this->getUser();  // может быть null
if ($securityUser) {
    $user = $this->userRepository->findByUsername($securityUser->getUserIdentifier());
    if ($user) {
        $quest['isLikedByCurrentUser'] = $this->questLikeService->isLiked($user->getId(), $questId);
    }
} else {
    $quest['isLikedByCurrentUser'] = false;
}
```

**Важно:** `$this->getUser()` возвращает `UserInterface|null`, а не полный User entity. Для получения полного entity используйте `UserRepository::findByUsername()`.

## ⚠️ Типичные ошибки

### "The string did not match the expected pattern"
**Причина:** Frontend отправляет `email` вместо `username`  
**Решение:** Убедитесь что LoginData использует `username`

### 401 Unauthorized
**Причина:** Токен отсутствует/невалиден/истек  
**Решение:** Проверьте наличие токена в localStorage

### 409 Conflict
**Причина:** Username или email уже существует  
**Решение:** Используйте другой username/email

## 📚 Документация

- **Полная документация:** `memory-bank/systemPatterns.md` → Authentication Patterns
- **API Reference:** `memory-bank/techContext.md` → API Endpoints
- **Задача:** `memory-bank/tasks.md` → CQST-007

---

**Обновлено:** 2025-12-07

