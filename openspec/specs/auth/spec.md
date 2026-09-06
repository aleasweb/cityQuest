# auth Specification

## Purpose
Определяет механизмы аутентификации, управления сессиями и защиты маршрутов, используемые в проекте CityQuest.

## Requirements

### Requirement: User Registration
Система ДОЛЖНА позволять пользователям регистрироваться с уникальными email и username, а также паролем. 
Система ДОЛЖНА строго валидировать входные данные.

#### Scenario: Successful registration
- **WHEN** клиент отправляет валидные данные (email, username, password) на эндпоинт регистрации
- **THEN** система создает пользователя и возвращает код 201 Created с данными профиля
- **TESTS**:
  - Backend: `AuthControllerTest::testSuccessfulRegistration`

#### Scenario: Registration with existing email or username
- **WHEN** клиент отправляет данные с уже занятым email или username
- **THEN** система возвращает ошибку 409 Conflict
- **TESTS**:
  - Backend: `AuthControllerTest::testRegistrationWithExistingEmail`
  - Backend: `AuthControllerTest::testRegistrationWithExistingUsername`

#### Scenario: Registration with invalid data
- **WHEN** клиент отправляет невалидные данные (некорректный email, короткий пароль, недопустимые символы в username, пустые поля)
- **THEN** система возвращает ошибку 400 Bad Request с массивом нарушений валидации
- **TESTS**:
  - Backend: `AuthControllerTest::testRegistrationWithInvalidEmail`
  - Backend: `AuthControllerTest::testRegistrationWithShortPassword`
  - Backend: `AuthControllerTest::testRegistrationWithShortUsername`
  - Backend: `AuthControllerTest::testRegistrationWithInvalidUsernameCharacters`
  - Backend: `AuthControllerTest::testRegistrationWithMissingFields`

### Requirement: JWT Authentication via HttpOnly Cookies
Система ДОЛЖНА использовать JWT (JSON Web Token) для аутентификации пользователей, передавая токен исключительно через HttpOnly cookies для защиты от XSS-атак.

#### Scenario: Successful authentication
- **WHEN** клиент отправляет валидные учетные данные на эндпоинт логина
- **THEN** сервер возвращает успешный ответ и устанавливает HttpOnly cookie с JWT
- **TESTS**:
  - Backend: `AuthControllerTest::testSuccessfulLogin`

### Requirement: CORS and Credentials
API ДОЛЖНО принимать запросы с аутентификацией только от разрешенных origin (whitelist) и требовать передачи credentials (cookies) во всех защищенных запросах.

#### Scenario: Cross-origin request with credentials
- **WHEN** фронтенд делает запрос к защищенному эндпоинту с `credentials: 'include'`
- **THEN** сервер обрабатывает запрос, читая токен из cookie

### Requirement: Protected Endpoints Access
Система ДОЛЖНА ограничивать доступ к защищенным эндпоинтам (таким как профиль, прогресс, лайки), требуя наличия валидного JWT.

#### Scenario: Accessing protected endpoint without token
- **WHEN** неаутентифицированный клиент запрашивает данные профиля пользователя
- **THEN** система возвращает ошибку 401 Unauthorized
- **TESTS**:
  - Backend: `ProfileControllerTest::testGetProfileRequiresAuthentication`

#### Scenario: Accessing protected endpoint with expired token
- **WHEN** клиент запрашивает защищенный ресурс с истекшим JWT
- **THEN** система возвращает ошибку 401 Unauthorized

#### Scenario: Accessing forbidden resource
- **WHEN** аутентифицированный клиент пытается получить доступ к ресурсу, на который у него нет прав (например, чужой профиль)
- **THEN** система возвращает ошибку 403 Forbidden
