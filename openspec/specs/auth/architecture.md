# Архитектура домена Auth

Домен `auth` (в коде бэкенда представлен `AuthController`) отвечает за регистрацию, аутентификацию, управление сессиями.

## 1. Обзор архитектуры (DDD)

Домен реализован в соответствии с принципами Domain-Driven Design (DDD) и располагается в `project/src/User/`.

**Слои:**
- **Domain:** Содержит сущность `User`, интерфейс репозитория `UserRepositoryInterface` и доменные исключения (`InvalidCredentialsException`, `UserAlreadyExistsException`).
- **Application:** Сервисы (`AuthenticationService`), DTO (`RegisterUserRequest`) и обработчики событий (`UserWasRegisteredHandler`).
- **Infrastructure:** Реализация репозитория `DoctrineUserRepository`, подписчик `JWTAuthenticationSubscriber`.
- **Presentation:** REST-контроллеры `AuthController`,

## 2. Механизм аутентификации и сессий

Система использует **JWT (JSON Web Token)**, но для защиты от XSS-атак токены **не хранятся** в `localStorage` на фронтенде.

### 2.1 HttpOnly Cookies
Вместо заголовка `Authorization: Bearer`, токен передается через HttpOnly cookie `jwt_token`.
- **Конфигурация LexikJWT:** Настроена секция `set_cookies` с флагами `httpOnly: true` и `samesite: strict`.
- **Извлечение токена:** Настроено в `token_extractors.cookie`.

### 2.2 CORS и Credentials
Для работы с cookies в cross-origin запросах (если фронтенд и бэкенд на разных портах/доменах):
- Бэкенд (`nelmio_cors.yaml`) настроен с `allow_credentials: true` и строгим `allow_origin` (whitelist).
- Фронтенд обязан во все защищенные запросы добавлять `credentials: 'include'`.

### 2.3 Механизм Login
`POST /api/auth/login`
Этот эндпоинт не имеет собственной реализации в контроллере. Он полностью перехвачен и обрабатывается связкой Symfony Security (json_login) и LexikJWTAuthenticationBundle.
- Фронтенд отправляет POST-запрос с JSON-телом: `{"username": "...", "password": "..."}.`
- Symfony проверяет учетные данные через app_user_provider.
- В случае успеха LexikJWTAuthenticationBundle генерирует JWT-токен.
- Токен автоматически помещается в HttpOnly куку с названием jwt_token (срок жизни — 1 час, SameSite=Strict).
- Срабатывает кастомный подписчик JWTAuthenticationSubscriber, который добавляет в JSON-ответ данные авторизованного пользователя.

### 2.4 Механизм Logout
`POST /api/auth/logout`
Этот эндпоинт реализован вручную в AuthController::logout().
- Фронтенд отправляет POST-запрос.
- Контроллер формирует успешный JSON-ответ `({"message": "Logged out successfully"}).`
- К ответу прикрепляется заголовок Set-Cookie, который перезаписывает куку jwt_token, делая её пустой и устанавливая срок действия в прошлое (1 января 1970 года).
- Браузер получает ответ и автоматически удаляет куку с токеном. Сессия завершена.

### 2.5 Механизм проверки текущего пользователя
`GET /api/auth/me`
Так как токен хранится в куках, фронтенд не может прочитать его напрямую, чтобы узнать, авторизован ли пользователь (например, после перезагрузки страницы). Реализация на backend в AuthController.
- Фронтенд делает GET-запрос (с credentials: 'include', чтобы браузер прикрепил куку jwt_token).
- Бекенд извлекает токен из куки, валидирует его и возвращает данные текущего пользователя.
- Если куки нет или токен протух, возвращается 401 Unauthorized.

## 3. Особенности для фронтенда

Важно: для корректной работы всего механизма на фронтенде во все fetch-запросы к API необходимо добавлять настройку credentials: 'include', иначе браузер не будет отправлять куку с токеном.

## 4. Особенности работы портала с авторизацией

## 4.1. Обязательная авторизация

Обеспечивается через `AuthenticationTrait::getAuthenticatedUserOr401Response`

## 4.1. Опциональная авторизация

Некоторые эндпоинты (например, просмотр квестов `GET /api/quests/{id}`) поддерживают опциональную авторизацию. Они доступны всем, но возвращают расширенные данные, если пользователь авторизован (например, статус лайка `isLikedByCurrentUser`).

**Реализация:**
- В `security.yaml` для таких маршрутов установлено `jwt: ~` и `roles: PUBLIC_ACCESS`.
- В контроллере проверяется `$this->getUser()`.
- **Важно:** `$this->getUser()` возвращает `UserInterface|null`. Для получения полного entity используется `UserRepository::findByUsername($securityUser->getUserIdentifier())`.

## 5. API Endpoints

### Регистрация
`POST /api/auth/register`
- Принимает: `email`, `username`, `password`.
- Возвращает: `201 Created` и данные пользователя в формате

### Вход (Login)
`POST /api/auth/login`
- Принимает: `username`, `password` (обратите внимание, используется `username`, а не `email`).
- Возвращает: `200 OK` и устанавливает HttpOnly cookie `jwt_token`. Формат ответа
```
{
"token": "...", // (токен также дублируется в теле по умолчанию Lexik, но фронтенд должен опираться на куку)
"user": {
    "id": "uuid...",
    "email": "user@example.com",
    "username": "user",
    "createdAt": "2026-09-06 12:00:00"
    }
}
```

### Выход (Logout)
`POST /api/auth/logout`
- Требует: Наличие валидной cookie.
- Возвращает: `200 OK` и очищает cookie `jwt_token`.

## 6. Связь с другими доменами

- todo
