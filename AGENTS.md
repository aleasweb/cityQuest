# CityQuest — AI Agent Guide

## Проект

Интерактивные квесты с привязкой к реальным локациям (Web, iOS, Android).  
Аудитория: 18–45 лет, местные жители и туристы.

## Стек

| Слой | Технологии |
|------|-----------|
| Backend | Symfony 6.4, PHP 8.3, Doctrine ORM 3, PostgreSQL 16, Lexik JWT |
| Frontend | React 19, TypeScript 5.8, Vite 6.3, Tailwind 3.4, React Router 7.5, Zod |
| Infra | Docker Compose (nginx, php-fpm, postgres); Mobile: Flutter (planned) |

## Структура

```
project/src/          # Backend (DDD)
  User/               # Auth, профиль
  Quest/              # Квесты, лайки
  UserProgress/       # Прогресс + Event Sourcing
  Platform/           # Определение платформы (web/ios/android)
  City/               # Справочник городов
  Shared/             # Общие трейты/интерфейсы
frontend/web/src/     # React SPA
openspec/             # Спецификации и изменения
  specs/              # Основные specs (domain truth)
  changes/            # Активные изменения (delta specs + tasks)
```

## Архитектура

- **DDD bounded contexts:** User / Quest / UserProgress / City / Platform / Shared/Geo
- **Слои:** Domain → Application → Infrastructure → Presentation
- **UUID PK** для агрегатов; QuestStep использует `INTEGER`
- **Event Sourcing** для UserProgress (`domain_events_progress`, append-only)
- **REST API:** envelope `{ "data": ..., "meta"?: ... }`, JWT через HttpOnly cookies
- **CORS:** `credentials: 'include'`, whitelist origin фронтенда

## Ключевые соглашения

### Backend (PHP)
- PSR-12, `strict_types`, `final` классы по умолчанию
- PHPStan Level 5; PHP-CS-Fixer
- Domain Layer — без зависимостей на Symfony/Doctrine
- Domain exceptions наследуют `\DomainException`
- Миграции Doctrine **обязательно** синхронизировать с `data/init-db/cityquest.sql`

### Frontend (TS)
- Zod-схемы для всех API-ответов
- `credentials: 'include'` во всех fetch-запросах
- `CacheManager` (LocalStorage, TTL 1ч) для `/api/cities`
- UI: primary `#ed8e34`, Inter, сетка 8px

### API
- Публичные эндпоинты: квесты, города, health
- Защищённые (JWT): профиль, прогресс, лайки
- Ошибки клиента: `400 / 401 / 403 / 404 / 409 / 422`

## Команды

```bash
# Запуск
make up

# Тесты (ТОЛЬКО в Docker)
docker compose exec php-fpm php bin/phpunit

# PHPStan
docker compose exec php-fpm vendor/bin/phpstan analyse

# Миграция
docker compose exec php-fpm php bin/console doctrine:migrations:migrate

# Frontend
cd frontend/web && npm run dev
```

⚠️ **Никогда не запускай** `php bin/phpunit` локально — тесты зависят от PostgreSQL в Docker.

## Workflow OpenSpec

Изменения живут в `openspec/changes/<slug>/`:
- `proposal.md` — дизайн и цели
- `delta-spec.md` — изменения спецификации
- `tasks.md` — задачи по реализации

Команды: `/opsx-propose`, `/opsx-apply`, `/opsx-sync`, `/opsx-archive`.  
**Документация OpenSpec ведётся только на русском языке.**

### Правила ведения документации и тестов
При планировании (`/opsx-propose`) и реализации (`/opsx-apply`) изменений агент обязан:
1. **Обновление архитектуры:** В генерируемом `tasks.md` всегда создавать отдельную задачу на обновление глобальной архитектурной документации по домену (`openspec/specs/<domain>/architecture.md`).
2. **Связь тестов со спецификациями:** В `tasks.md` всегда добавлять задачу на простановку ссылок на новые тесты в дельта-спецификации.
   Пример генерации задач:
   ```markdown
   ## 2. Documentation Sync
   - [ ] 2.1 Обновить `openspec/specs/<domain>/architecture.md`, добавив описание новых архитектурных решений.
   - [ ] 2.2 Добавить ссылки на новые тесты (`AuthControllerTest.php`) в соответствующие сценарии в `openspec/changes/<change-name>/specs/<domain>/spec.md`.
   ```
3. **Реализация тестов:** При написании тестов для backend/frontend создавать их строго на основе сценариев из `spec.md`. После создания тестов агент должен обновить дельта-спецификацию изменения, добавив под каждым протестированным сценарием (`#### Scenario`) прямые markdown-ссылки на файлы тестов.

## Текущий статус (сентябрь 2026)

- ✅ Auth (JWT HttpOnly), профиль, список/детали квестов, геопоиск
- ✅ Прогресс: start / pause / complete / abandon
- ✅ Лайки (dedicated table), Event Sourcing для UserProgress
- ✅ Quest steps + геочекпоинты + автозавершение (backend)
- ⏳ Frontend ~70%; Mobile 0%
