# User Specification

## Purpose
Определяет механизмы управления профилем пользователя, просмотра публичных профилей в проекте CityQuest.

## Requirements

### Requirement: View Private Profile
Система ДОЛЖНА позволять авторизованному пользователю просматривать свои полные данные профиля, включая email.

#### Scenario: Accessing own profile
- **WHEN** авторизованный клиент запрашивает свой профиль (`GET /api/user/profile`)
- **THEN** система возвращает полные данные пользователя (`id`, `email`, `username`, `createdAt`)
- **TESTS**:
  - Backend: `ProfileControllerTest::testGetProfile`

#### Scenario: Accessing profile without authentication
- **WHEN** неаутентифицированный клиент запрашивает профиль
- **THEN** система возвращает ошибку 401 Unauthorized
- **TESTS**:
  - Backend: `ProfileControllerTest::testGetProfileRequiresAuthentication`

### Requirement: Update Private Profile
Система ДОЛЖНА позволять пользователю обновлять свои данные (например, email), при этом строго валидируя входные данные и проверяя уникальность.

#### Scenario: Successful profile update
- **WHEN** клиент отправляет валидный новый email (`PATCH /api/user/profile`)
- **THEN** система обновляет email и возвращает обновленные данные профиля
- **TESTS**:
  - Backend: `ProfileControllerTest::testUpdateProfile`

#### Scenario: Update with existing email
- **WHEN** клиент пытается изменить email на тот, который уже занят другим пользователем
- **THEN** система возвращает ошибку 409 Conflict
- **TESTS**:
  - Backend: `ProfileControllerTest::testUpdateProfileWithExistingEmail`

#### Scenario: Update with invalid data
- **WHEN** клиент отправляет невалидный email
- **THEN** система возвращает ошибку 400 Bad Request с описанием нарушений валидации
- **TESTS**:
  - Backend: `ProfileControllerTest::testUpdateProfileWithInvalidEmail`

### Requirement: View Public Profile
Система ДОЛЖНА предоставлять публичный доступ к базовой информации о любом пользователе по его `username` (без раскрытия чувствительных данных, таких как email).

#### Scenario: Accessing existing public profile
- **WHEN** клиент запрашивает публичный профиль существующего пользователя (`GET /api/users/{username}`)
- **THEN** система возвращает базовые данные (`id`, `username`, `createdAt`)

#### Scenario: Accessing non-existent public profile
- **WHEN** клиент запрашивает профиль с несуществующим `username`
- **THEN** система возвращает ошибку 404 Not Found

### Requirement: View Public Profile with Quest History
Система ДОЛЖНА позволять запрашивать публичный профиль вместе с историей квестов пользователя (активный, на паузе, последние завершенные).

#### Scenario: Accessing public profile with quest history
- **WHEN** клиент запрашивает публичный профиль с параметром `?includeQuests=true`
- **THEN** система возвращает базовые данные пользователя ПЛЮС:
  - `activeQuest` (текущий активный квест или null)
  - `pausedQuests` (массив квестов на паузе)
  - `completedQuests` (массив из максимум 5 последних завершенных квестов, отсортированных от новых к старым)
