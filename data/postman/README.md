# CityQuest API - Postman Collection

Полная коллекция API endpoints для тестирования и разработки CityQuest MVP.

## 📦 Содержание

### Файлы

1. **CityQuest-API.postman_collection.json** - Основная коллекция запросов
2. **CityQuest-Local.postman_environment.json** - Локальное окружение (разработка)
3. **CityQuest-Production.postman_environment.json** - Production окружение

## 🚀 Быстрый старт

### 1. Импорт в Postman

#### Способ 1: Через Postman App
1. Откройте Postman
2. Нажмите **Import** в верхнем левом углу
3. Перетащите файлы или выберите **Choose Files**
4. Импортируйте все 3 файла (коллекцию и оба environment)

#### Способ 2: Через URL (если файлы на GitHub)
1. Нажмите **Import** → **Link**
2. Вставьте URL к raw файлу
3. Нажмите **Continue** → **Import**

### 2. Выбор окружения

В правом верхнем углу Postman выберите окружение:
- **CityQuest - Local** - для локальной разработки
- **CityQuest - Production** - для production тестирования

### 3. Настройка локального окружения

Environment переменные уже настроены для локальной разработки:
- `base_url`: http://cityquest.test
- `user_email`: test@example.com
- `user_password`: testPassword123
- `user_username`: testuser
- `jwt_token`: (автоматически заполняется после логина)
- `public_username`: testuser (для тестирования публичных профилей)
- `new_user_email`: updated@example.com (для тестирования обновления профиля)

## 📚 Структура коллекции

### Authentication

#### 1. Register New User
- **Method:** POST
- **Endpoint:** `/api/auth/register`
- **Auth:** None
- **Body:**
  ```json
  {
    "email": "user@example.com",
    "password": "password123",
    "username": "username"
  }
  ```
- **Response (201):**
  ```json
  {
    "user": {
      "id": "uuid",
      "email": "user@example.com",
      "username": "username",
      "roles": ["ROLE_USER"],
      "createdAt": "2025-10-25T12:00:00+00:00"
    }
  }
  ```

**Автоматические тесты:**
- ✅ Статус 201
- ✅ Структура ответа
- ✅ Валидный UUID
- ✅ Роль ROLE_USER по умолчанию

#### 2. Login
- **Method:** POST
- **Endpoint:** `/api/auth/login`
- **Auth:** None
- **Body:**
  ```json
  {
    "username": "testuser",
    "password": "password123"
  }
  ```
- **Response (200):**
  ```json
  {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "email": "user@example.com",
      "username": "username",
      "roles": ["ROLE_USER"]
    }
  }
  ```

**Автоматические тесты:**
- ✅ Статус 200
- ✅ JWT token присутствует
- ✅ Token автоматически сохраняется в переменную `jwt_token`
- ✅ User data в ответе

#### 3. Logout
- **Method:** POST
- **Endpoint:** `/api/auth/logout`
- **Auth:** Bearer Token (автоматически)
- **Response (204):** Empty body

**Автоматические тесты:**
- ✅ Статус 204
- ✅ Пустой body
- ✅ Token автоматически удаляется из окружения

### User Profile

#### 1. Get My Profile
- **Method:** GET
- **Endpoint:** `/api/user/profile`
- **Auth:** Bearer Token (требуется)
- **Response (200):**
  ```json
  {
    "id": "uuid",
    "email": "user@example.com",
    "username": "username",
    "createdAt": "2025-10-26 12:00:00"
  }
  ```

**Автоматические тесты:**
- ✅ Статус 200
- ✅ Структура профиля (id, email, username, createdAt)
- ✅ Валидный UUID
- ✅ Email присутствует (приватный профиль)

#### 2. Get Public Profile
- **Method:** GET
- **Endpoint:** `/api/users/{{public_username}}`
- **Auth:** None (публичный endpoint)
- **Response (200):**
  ```json
  {
    "id": "uuid",
    "username": "username",
    "createdAt": "2025-10-26 12:00:00"
  }
  ```

**Автоматические тесты:**
- ✅ Статус 200
- ✅ Структура публичного профиля (id, username, createdAt)
- ✅ Email НЕ присутствует (публичный профиль)
- ✅ Валидный UUID

#### 3. Update My Profile
- **Method:** PATCH
- **Endpoint:** `/api/user/profile`
- **Auth:** Bearer Token (требуется)
- **Body:**
  ```json
  {
    "email": "newemail@example.com"
  }
  ```
- **Response (200):**
  ```json
  {
    "message": "Profile updated successfully",
    "user": {
      "id": "uuid",
      "email": "newemail@example.com",
      "username": "username",
      "createdAt": "2025-10-26 12:00:00"
    }
  }
  ```

**Автоматические тесты:**
- ✅ Статус 200
- ✅ Сообщение об успехе
- ✅ Обновленные данные пользователя
- ✅ Email был изменен

**Возможные ошибки:**
- `400` - Невалидный email формат
- `401` - Отсутствует JWT токен
- `404` - Пользователь не найден (для публичного профиля)
- `409` - Email уже занят

### Health Check

#### Health Check
- **Method:** GET
- **Endpoint:** `/health-check`
- **Auth:** None
- **Response (200):**
  ```json
  {
    "status": "OK"
  }
  ```

**Автоматические тесты:**
- ✅ Статус 200
- ✅ Status = OK
- ✅ Время ответа < 500ms

## 🔐 Аутентификация

### JWT Token Flow

1. **Получение токена:**
   - Зарегистрируйтесь или войдите
   - JWT token автоматически сохраняется в переменную `jwt_token`

2. **Использование токена:**
   - Все защищенные endpoints автоматически используют токен
   - Authorization header: `Bearer {{jwt_token}}`

3. **Очистка токена:**
   - Выполните Logout
   - Token автоматически удаляется из переменной окружения

### Ручное управление токеном

Если нужно вручную установить токен:
1. Перейдите в Environments
2. Выберите активное окружение
3. Найдите переменную `jwt_token`
4. Вставьте значение токена

## 🧪 Автоматическое тестирование

Каждый request включает автоматические тесты (Tests tab в Postman).

### Глобальные тесты (для всех запросов):
- ✅ Время ответа < 3000ms
- ✅ Content-Type header присутствует

### Специфичные тесты:
- **Register:** Валидация структуры user, UUID, роли
- **Login:** Проверка токена, автосохранение
- **Logout:** Проверка статуса, очистка токена
- **Get My Profile:** Валидация приватного профиля с email
- **Get Public Profile:** Валидация публичного профиля без email
- **Update Profile:** Проверка обновления данных
- **Health:** Проверка статуса и времени ответа

### Запуск всех тестов

#### Через Collection Runner:
1. Кликните на коллекцию "CityQuest API"
2. Нажмите **Run**
3. Выберите окружение (Local/Production)
4. Нажмите **Run CityQuest API**
5. Postman автоматически выполнит все запросы и тесты

#### Через Newman (CLI):
```bash
# Установка Newman
npm install -g newman

# Запуск коллекции
newman run CityQuest-API.postman_collection.json \
  -e CityQuest-Local.postman_environment.json

# С HTML отчетом
newman run CityQuest-API.postman_collection.json \
  -e CityQuest-Local.postman_environment.json \
  -r html
```

## 📝 Примеры использования

### Сценарий 1: Регистрация нового пользователя

1. Откройте request "Register New User"
2. Измените переменные в окружении или в body запроса:
   ```json
   {
     "email": "newuser@example.com",
     "password": "securePass123",
     "username": "newuser"
   }
   ```
3. Нажмите **Send**
4. Проверьте Tests (должны быть зелеными ✅)

### Сценарий 2: Вход и получение токена

1. Откройте request "Login"
2. Используйте email/password из регистрации
3. Нажмите **Send**
4. JWT token автоматически сохранится в переменную `jwt_token`
5. Теперь можно использовать защищенные endpoints

### Сценарий 3: Полный flow (регистрация → вход → выход)

1. **Register New User** → Создание аккаунта
2. **Login** → Получение JWT token
3. (Здесь можно добавить защищенные endpoints)
4. **Logout** → Очистка токена

## 🔍 Примеры ответов

В каждом request есть раздел **Examples** с примерами успешных и ошибочных ответов:

### Register - Success
```json
{
  "user": {
    "id": "9d7f8a2b-3c4e-4f5a-8b6c-1d2e3f4a5b6c",
    "email": "john.doe@example.com",
    "username": "johndoe",
    "roles": ["ROLE_USER"],
    "createdAt": "2025-10-25T12:00:00+00:00"
  }
}
```

### Register - Duplicate Email (409)
```json
{
  "error": "User with email \"existing@example.com\" already exists"
}
```

### Register - Validation Error (400)
```json
{
  "violations": {
    "email": "Invalid email format",
    "password": "This value is too short. It should have 8 characters or more.",
    "username": "This value is too short. It should have 3 characters or more."
  }
}
```

### Login - Invalid Credentials (401)
```json
{
  "code": 401,
  "message": "Invalid credentials."
}
```

## ⚙️ Переменные окружения

### Локальное окружение (CityQuest - Local)

| Переменная | Значение | Описание |
|------------|----------|----------|
| `base_url` | http://cityquest.test | Базовый URL API |
| `user_email` | test@example.com | Email для тестирования |
| `user_password` | testPassword123 | Пароль для тестирования |
| `user_username` | testuser | Username для тестирования |
| `jwt_token` | (auto) | JWT token (заполняется автоматически) |

### Production окружение (CityQuest - Production)

| Переменная | Значение | Описание |
|------------|----------|----------|
| `base_url` | https://api.cityquest.com | Production URL |
| `user_email` | (пусто) | Ваш production email |
| `user_password` | (пусто) | Ваш production пароль |
| `user_username` | (пусто) | Ваш production username |
| `jwt_token` | (auto) | JWT token (заполняется автоматически) |

⚠️ **Важно:** Не храните production credentials в Git! Заполняйте их локально в Postman.

## 🛠️ Troubleshooting

### Проблема: "Could not get any response"

**Решение:**
1. Проверьте, что Docker контейнеры запущены:
   ```bash
   docker-compose ps
   ```
2. Проверьте доступность API:
   ```bash
   curl http://cityquest.test/health-check
   ```
3. Проверьте `/etc/hosts` (должна быть запись для cityquest.test)

### Проблема: "401 Unauthorized" на защищенных endpoints

**Решение:**
1. Выполните Login для получения токена
2. Проверьте, что переменная `jwt_token` заполнена:
   - Environments → Выберите окружение → Проверьте `jwt_token`
3. Убедитесь, что токен не истек (TTL = 1 час)

### Проблема: Тесты падают (красные ❌)

**Решение:**
1. Проверьте базу данных (возможно нужна очистка):
   ```bash
   docker-compose exec db psql -U user -d cityquest_test -c "TRUNCATE TABLE users CASCADE;"
   ```
2. Измените тестовые данные (email/username должны быть уникальными)
3. Проверьте Console в Postman для деталей ошибки

## 📊 CI/CD Integration

### Newman в CI Pipeline

```yaml
# .github/workflows/api-tests.yml
name: API Tests

on: [push, pull_request]

jobs:
  api-tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      
      - name: Install Newman
        run: npm install -g newman newman-reporter-htmlextra
      
      - name: Start Services
        run: docker-compose up -d
      
      - name: Wait for API
        run: sleep 10
      
      - name: Run API Tests
        run: |
          newman run data/postman/CityQuest-API.postman_collection.json \
            -e data/postman/CityQuest-Local.postman_environment.json \
            -r htmlextra,cli \
            --reporter-htmlextra-export newman-report.html
      
      - name: Upload Report
        uses: actions/upload-artifact@v2
        with:
          name: newman-report
          path: newman-report.html
```

## 📖 Дополнительная информация

### Документация проекта
- **API Spec:** `memory-bank/mvp-spec.md`
- **Architecture:** `memory-bank/mvp-backend-structure.md`
- **Reflection:** `memory-bank/reflection/reflection-CQST-001.md`
- **Archive:** `memory-bank/archive/archive-CQST-001-20251025.md`

### Postman Features используемые в коллекции
- ✅ Environment Variables
- ✅ Pre-request Scripts
- ✅ Test Scripts
- ✅ Auto token management
- ✅ Example responses
- ✅ Request descriptions
- ✅ Global authentication
- ✅ Collection variables

## 🎯 Следующие шаги

После успешного тестирования Authentication endpoints, будут добавлены:

1. **User Profile Endpoints** (CQST-002)
   - GET /api/users/me
   - PATCH /api/users/me
   - DELETE /api/users/me

2. **Quest Endpoints** (CQST-003)
   - CRUD operations for quests
   - Quest progress tracking

3. **Achievement Endpoints**
   - User achievements
   - Progress tracking

---

**Версия:** 1.0.0  
**Дата создания:** 2025-10-25  
**Последнее обновление:** 2025-10-25  
**Maintainer:** CityQuest Development Team
