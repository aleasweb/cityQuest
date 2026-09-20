# Proposal: Mobile Flutter Client (Greenfield)

## Summary
Создать с нуля (greenfield) мобильное приложение CityQuest для iOS/Android на Flutter, полностью поверх существующего REST API (JWT HttpOnly cookies, envelope `{data, meta}`). Мобильный клиент — независимый проект, не разделяющий код с веб-фронтендом: свой API-слой, своя дизайн-система (перенос UI-токенов из веба), своя навигация.

## Motivation
- Мобильная платформа — 0% реализации при готовом backend.
- Основной сценарий продукта — квесты с геопозицией — естественно выполняется на мобильном устройстве (GPS, push, камера).
- Greenfield позволяет внедрить современный стек и чистую архитектуру без наследия веб-фронтенда.

## Goals
- Рабочий MVP на обеих платформах: регистрация/вход, каталог квестов (фильтры + nearby), деталь квеста, движок прохождения с геочекпоинтами, профиль и история.
- Feature-driven архитектура с изолированными доменными модулями.
- Использование `X-App-Platform` заголовка для аналитики платформы (уже поддерживается backend).
- Развертывание: dev-сборки iOS (Simulator/xcodebuild) и Android (APK/AAB).

## Non-Goals
- Не рефакторить backend; только точечные изменения (аутентификация на устройствах, если Cookie/WebView блокируются — fallback на Bearer токен из тела ответа).
- Не использовать WebView/обёртки (Cordova/Capacitor): только нативный Flutter.
- Без оффлайна квестов в этой итерации (пока тёплый кеш справочников и кэш последних данных).
- Без push-уведомлений, без оплат, без соц.авторизаций в MVP.
- Без кастомной карты/тактильных интерфейсов — используем `flutter_map` (OSM), геочекпоинты как основная механика.

## Tech Stack (greenfield)
| Компонент | Выбор | Причина |
|---|---|---|
| Framework | Flutter 3.x / Dart 3 | Единый код для iOS+Android из коробки |
| State & DI | `riverpod` 2.x + `riverpod_generator` | Compile-safe, встроенная DI, нет ScopedModel |
| Networking | `dio` + `dio_cookie_manager` | HttpOnly JWT cookies из коробки (как браузер) |
| Сериализация | `freezed` + `json_serializable` | Иммутабельные DTO, паттерн-матчинг |
| Routing | `go_router` | Deeplinks, типизированные route-params |
| Карта/GPS | `flutter_map` (OSM) + `geolocator` | Open-source, бесшовный трекинг |
| Storage | `shared_preferences` + `hive` | Кеш справочников (TTL 1ч как в вебе), кеш профиля |
| Локализация | `flutter_localizations` (ru) + `intl` | Продукт — российский рынок |

## Architecture
Каталог: `mobile/` (рядом с `frontend/web/`). Feature-driven:

```
mobile/
  app/                    # собираемый Flutter-апп (main.dart, корневой app)
    core/
      api/                # Dio-клиент, cookie manager, envelope-перехватчики, PlatformResolver
      config/             # dev/stage/prod конфиги, эндпоинты
      storage/            # CacheManager (TTL), hive-боксы
      design/             # тема (primary #ed8e34, Inter, сетка 8px), токены
      router/             # go_router конфигурация, route guards
      network/            # монитор сети, retry-policies
    shared/               # shared виджеты (QuestCard, Toast, Skeleton), результат-типы
    features/
      auth/               # login/register/session/logout (data/domain/presentation)
      quests/             # каталог, фильтры, nearby, деталь
      progress/           # движок: активный квест, GPS-трекинг, чекпоинты, start/pause/complete/abandon
      profile/            # мой профиль, история, лайки
    bootstrap/            # init: настройка кеша, DI-контейнер, запуск
  packages/               # опциональные внутренние пакеты (lints, models) — только при необходимости
```

Каждый feature: `domain/` (чистые Dart-модели, абстрактные репозитории), `data/` (DTO, реализации репозиториев, API), `application/` (use cases, Riverpod-контроллеры) и `presentation/` (UI, виджеты).

## Integration Points (backend)
- Auth: `/api/auth/login|register|logout|me` — cookies через dio_cookie_manager; fallback `Authorization: Bearer` из тела ответа.
- Quests: `/api/quests` (фильтры), `/api/quests/nearby`, `/api/quests/{id}`, `/api/quests/{id}/like`.
- Steps: `/api/quests/{questId}/steps/{stepNumber}`.
- Progress: `/api/user/progress.../start|pause|complete|check|DELETE`.
- Cities: `/api/cities` (public, кеш 1ч).
- Platform header: `X-App-Platform: ios/1.0.0` или `android/1.0.0` во всех запросах.

## Milestones / Sprints
1. **Sprint 0 — Bootstrap:** scaffolding, CI, сборка empty-app на iOS+Android, design tokens.
2. **Sprint 1 — Core & Auth:** dio/cookies, auth flow, состояние сессии, профиль-мини.
3. **Sprint 2 — Discovery:** каталог квестов, фильтры, nearby, деталь квеста, города-кеш.
4. **Sprint 3 — Quest Engine:** карта маршрута, GPS-трекинг, чекпоинты, start/pause/abandon/complete.
5. **Sprint 4 — Profile & Polish:** история, лайки, офлайн-обработка, пермишены, релизные сборки.

## Dependencies outside scope
- Доступ к API-серверу из приложения: dev через `localhost`/туннель, prod через HTTPS-домен с Cookie-политиками `Secure; SameSite=None` (обновление `lexik_jwt_authentication.yaml` — задел, решается на sprint 0/1).
- CORS не нужен для нативных клиентов (не браузер).

## Open Questions
- Выбор картового провайдера: OSM (default) vs Mapbox SDK vs Yandex Maps (региональное качество). См. `tasks.md` #S0-6.
- Политика Secure cookies для prod-поддомена мобильного API (решается как задача backend на sprint 0).