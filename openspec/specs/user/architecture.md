# Архитектура домена User

Домен `user` отвечает за управление профилем пользователя, просмотр публичных профилей. 
*Примечание: Механизмы аутентификации и сессий (регистрация, логин, токены) выделены в отдельный домен `auth`.*

## 1. Обзор архитектуры (DDD)

Домен реализован в соответствии с принципами Domain-Driven Design (DDD) и располагается в `project/src/User/` (физически разделяя директорию с доменом `auth`).

**Слои:**
- **Domain:** Сущность `User` (агрегат), интерфейс `UserRepositoryInterface`, доменные исключения (`UserNotFoundException`, `UserAlreadyExistsException`).
- **Application:** Сервис `ProfileService`, DTO `UpdateProfileRequest`.
- **Infrastructure:** Реализация репозитория `DoctrineUserRepository`.
- **Presentation:** REST-контроллер `ProfileController`.

## 2. Основные компоненты

### 2.1. Сущность User
Агрегат `User` содержит основные данные пользователя: `id` (UUID), `email`, `username`, `password` (хеш), `roles`, `createdAt`, `updatedAt`. 
- `email` и `username` уникальны.
- Пароль хешируется и проверяется механизмами Symfony Security (в рамках домена `auth`).

### 2.2. Управление профилем (ProfileService)
Сервис `ProfileService` инкапсулирует бизнес-логику работы с профилями:
- **Приватный профиль:** Возвращает полные данные (включая `email`) для авторизованного пользователя.
- **Публичный профиль:** Возвращает ограниченный набор данных (`id`, `username`, `createdAt`) по `username`.
- **История квестов:** Агрегирует данные из доменов `UserProgress` и `Quest`, возвращая активные, поставленные на паузу и последние завершенные квесты пользователя.

## 3. Взаимодействие с другими доменами

Домен `user` тесно интегрирован с другими частями системы:

- **Auth:** `auth` отвечает за выдачу JWT и проверку сессий. В `ProfileController` используется атрибут `#[CurrentUser]` из Symfony Security для получения текущего пользователя на основе токена.
- **UserProgress:** Для формирования публичного профиля с историей квестов (`includeQuests=true`) `ProfileService` обращается к `UserQuestProgressRepositoryInterface` (поиск активных, на паузе и завершенных квестов).
- **Quest:** Для обогащения истории квестов названиями, картинками и городами используется `QuestRepositoryInterface`.

## 4. API Endpoints

### 4.1. Приватный профиль (свой)
`GET /api/user/profile`
- **Доступ:** Только авторизованные пользователи (JWT).
- **Возвращает:** `200 OK` и полные данные профиля.

**Пример ответа:**
```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "email": "user@example.com",
  "username": "user123",
  "createdAt": "2026-09-06 12:00:00"
}
```
- **Ошибки:** `401 Unauthorized` (если токен отсутствует или невалиден).

### 4.2. Обновление профиля
`PATCH /api/user/profile`
- **Доступ:** Только авторизованные пользователи (JWT).
- **Принимает:** JSON с полями для обновления (на данный момент поддерживается `email`).

**Пример запроса:**
```json
{
  "email": "new-email@example.com"
}
```

**Пример успешного ответа (`200 OK`):**
```json
{
  "message": "Profile updated successfully",
  "user": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "email": "new-email@example.com",
    "username": "user123",
    "createdAt": "2026-09-06 12:00:00"
  }
}
```

- **Ошибки:** 
  - `400 Bad Request` (ошибка валидации: неверный формат email). Возвращает массив `violations`.
  - `401 Unauthorized` (нет доступа).
  - `409 Conflict` (email уже занят другим пользователем).

### 4.3. Публичный профиль
`GET /api/users/{username}`
- **Доступ:** Публичный.
- **Параметры:** `?includeQuests=true` (опционально) для включения истории квестов.

**Пример ответа (без истории, `200 OK`):**
```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "username": "user123",
  "createdAt": "2026-09-06 12:00:00"
}
```

**Пример ответа (с историей `?includeQuests=true`, `200 OK`):**
```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "username": "user123",
  "createdAt": "2026-09-06 12:00:00",
  "activeQuest": {
    "quest": {
      "id": "123e4567-e89b-12d3-a456-426614174000",
      "title": "Тайны старого города",
      "imageUrl": "https://example.com/image.jpg",
      "difficulty": "medium",
      "city": "Москва"
    },
    "status": "in_progress",
    "startedAt": "2026-09-06 10:00:00",
    "completedAt": null
  },
  "pausedQuests": [],
  "completedQuests": [
    {
      "quest": {
        "id": "987e6543-e21b-34d5-c678-426614174000",
        "title": "Легенды Кремля",
        "imageUrl": "https://example.com/kremlin.jpg",
        "difficulty": "hard",
        "city": "Москва"
      },
      "status": "completed",
      "startedAt": "2026-09-05 14:00:00",
      "completedAt": "2026-09-05 16:30:00"
    }
  ]
}
```

- **Ошибки:** `404 Not Found` (если пользователь с таким username не найден).
