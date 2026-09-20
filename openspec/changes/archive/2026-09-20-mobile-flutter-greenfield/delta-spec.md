# Delta Spec: Mobile Flutter Client

## Summary
Добавляет новый домен спецификации `mobile-app` — мобильный клиент CityQuest на Flutter (iOS/Android) с feature-driven архитектурой поверх существующего REST API.

## Changes

### Added: `openspec/specs/mobile-app/spec.md`
Новая доменная спецификация поведения мобильного клиента:
- A2: Аутентификация и управление сессией на устройстве
- A3: Каталог квестов и геопоиск
- A4: Деталь квеста и лайки
- A5: Движок прохождения с геочекпоинтами
- A6: Профиль и история
- A7: Надёжность (оффлайн, кеш, пермишены)

### Added: `openspec/specs/mobile-app/architecture.md`
Архитектурное описание:
- Структура каталога `mobile/` (core / shared / features)
- Слои feature-модулей (domain/data/application/presentation)
- Взаимодействие с backend (сookie auth, X-App-Platform, envelope)
- Государственное управление через Riverpod (state providers, optimistic лайки)
- Кеширование (CacheManager TTL 1ч для городов)

## Links
- Изменение: `openspec/changes/mobile-flutter-greenfield/`
- План задач: `tasks.md`
- Proposal: `proposal.md`