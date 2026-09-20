# Архитектура домена Mobile App

Домен `mobile-app` — нативный мобильный клиент CityQuest для iOS/Android на Flutter (greenfield, каталог `mobile/`). Полностью независим от веб-фронтенда, взаимодействует с существующим REST API CityQuest.

## 1. Обзор архитектуры (Feature-driven)

Приложение организовано по принципу **feature-first** с изолированными бизнес-модулями:

```
mobile/
  app/
    core/          # фундаментальный слой (api, routing, storage, design)
    shared/        # общие виджеты и типы
    features/      # изолированные фичи: auth, quests, progress, profile, cities
    bootstrap/     # инициализация (DI, кеш, запуск)
```

Каждая фича — четырёхслойный модуль (Clean Architecture):
- **`domain/`** — чистые Dart-модели, бизнес-логика и абстрактные репозитории (без Flutter-зависимостей, тестируется unit-тестами).
- **`data/`** — DTO (freezed), API-клиенты (Dio), локальные хранилища (Hive) и реализации репозиториев.
- **`application/`** — use cases, координаторы и провайдеры бизнес-логики.
- **`presentation/`** — экраны, виджеты, UI-состояния через `AsyncValue` и Riverpod-providers.

**Правило изоляции:** Запрещён прямой импорт `presentation/` или `data/` между фичами. Фичи общаются через общие репозитории в `core/` или явные Riverpod-зависимости.

## 2. Состояние и DI (Riverpod)

- Глобальные провайдеры: `apiClientProvider`, `authControllerProvider`, `questsControllerProvider`, `progressControllerProvider`, `profileControllerProvider`.
- Riverpod используется как DI-контейнер для всех зависимостей (репозитории, API-клиенты). Запрещено использовать `get_it`.
- Состояния UI моделируются строго через `AsyncValue<T>` и freezed-классы. Избегать raw Future/Stream в presentation-слое.
- Реактивное состояние сессии управляет навигацией через route-guard go_router.
- **Оптимистичный лайк:** обновление UI до ответа сервера с откатом при ошибке.
- **Пример:** `QuestLikeService` инкапсулирует optimistic-логику лайка и revalidation квеста.

## 3. Сеть (Dio)

- Единый Dio-клиент с перехватчиками для:
  - envelope API `{ "data": ..., "meta"?: ... }`;
  - маппинга ошибок 400/401/403/404/409/422 в русские сообщения;
  - retry (1 attempt) на таймаут/5xx.
- **Cookies:** `dio_cookie_manager` + `path_provider` обеспечивают работу с HttpOnly JWT cookie идентично браузеру.
  - Fallback: если cookies заблокированы, использовать `Authorization: Bearer <token>` из тела ответа `/api/auth/login`.
- **Платформа:** во все запросы добавляется `X-App-Platform: ios/<version>` / `android/<version>` (используется backend для аналитики в Event Sourcing).
- CORS для нативного клиента не требуется.

## 4. Хранение (локально)

- `shared_preferences`: токены/флаги состояния сессии.
- `hive`: тёплый кеш по образцу веба —
  - `CacheManager` со справочником городов (`TTL 1 час`);
  - последние данные профиля (оффлайн-подсказка);
  - состояние активного квеста для повторной синхронизации при потере сети.

## 5. Геолокация и карта

- `geolocator` для GPS-доступа:
  - `ACCESS_FINE_LOCATION` (Android) / `NSLocationWhenInUseUsageDescription` (iOS);
  - `LocationAccuracy.high` во время прохождения квеста;
  - корректная обработка отказов (инлайн-подсказка, переход в настройки ОС).
- `flutter_map` (OpenStreetMap): маршрут по чекпоинтам, позиция пользователя, отображение радиуса срабатывания.

## 6. Навигация (go_router)

- Навигация полностью изолирована в `core/routing/` и не зависит от фич.
- Фичи экспортируют только route names (строгие константы/классы, без raw string literals).
- Вызов навигации происходит через `context.go()` или `ref.read(routerProvider)`. Запрещено навигировать из domain или repository слоёв.
- Маршруты: `splash → /auth` (login/register) | `/` (home) → `/quest/{id}` → `/quest-activity/{id}` (активный квест) → `/profile`.
- Route-guard: гость не попадает на `/profile` и активный квест, авторизованный автоматически на `/`.
- Deeplinks: `cityquest://quest/{id}` (задел).

## 7. Взаимодействие с backend (мэппинг)

| Backend endpoint | Использование |
|---|---|
| `POST /api/auth/login/register/logout`, `GET /api/auth/me` | Feature `auth` (cookies + fallback Bearer) |
| `GET /api/quests`, `/api/quests/nearby`, `/api/quests/{id}`, `/like` | Feature `quests` |
| `GET /api/quests/{questId}/steps/{stepNumber}` | Feature `progress` (данные чекпоинтов) |
| `GET/POST/PATCH/DELETE /api/user/progress...` | Feature `progress` (движок жизненного цикла) |
| `GET /api/cities` | Feature `cities` (CacheManager TTL 1ч) |
| `GET/PATCH /api/user/profile`, `GET /api/users/{username}` | Feature `profile` |

## 8. Обработка ошибок и Качество

- **Ошибки:** Ошибки API и хранилищ маппятся на domain-исключения (`sealed class Failure`). UI обрабатывает Failure через `AsyncValue.error`.
- `flutter analyze` (flutter_lints) — 0 warnings.
- GitHub Actions: analyze → `flutter build apk --debug` (тест-пайплайн) на push/PR.
- Backend-точечные изменения (Secure cookies) проверяются в Docker PHPUnit + PHPStan.

## 9. Релизный процесс (Sprint 4)

- **iOS:** `flutter build ios --release`, Info.plist-пермишены, значок/сплэш.
- **Android:** `flutter build appbundle`, манифест-пермишены, signing, R8/ProGuard.
- Smoke-тест на физических устройствах.