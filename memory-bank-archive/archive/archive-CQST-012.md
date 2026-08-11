# TASK ARCHIVE: CQST-012

## METADATA
- **Task ID:** CQST-012
- **Title:** Quest Steps Implementation (Система чекпоинтов)
- **Complexity Level:** Level 3 - Intermediate Feature
- **Created Date:** 2026-01-11
- **Completed Date:** 2026-08-11
- **Domain:** Quest / UserProgress / Shared(Geo)

## SUMMARY
Реализована система чекпоинтов для квестов, которая позволяет разбивать квест на последовательность шагов с валидацией геолокации пользователя. Добавлена новая сущность `QuestStep` (внутри домена Quest), добавлен независимый модуль `Shared/Geo` с сервисом геолокации. Реализована логика отслеживания прогресса (поле `current_step_number`), валидации шагов через формулу Haversine, записи истории (Event Sourcing) и автоматического завершения квеста по достижении последнего шага.

## REQUIREMENTS
- **Функциональные:**
  1. Сущность `QuestStep` с координатами, контентом, радиусом, привязанная к квесту без строгого FK для независимости.
  2. Новое поле `type` в сущности `Quest` (Linear/Random), по умолчанию 'linear'.
  3. Отслеживание `current_step_number` в сущности `UserQuestProgress`.
  4. Endpoint: GET `/api/quests/{questId}/steps/{stepId}` - только для пользователей с активным квестом.
  5. Endpoint: POST `/api/user/progress/{questId}/check` - валидация геолокации (Haversine formula + radius) и переход к следующему шагу или автозавершение квеста.
  6. Модификация POST `/start`: запись первого активного шага в прогресс.
  7. Интеграция с существующей системой Event Sourcing (генерация `QuestStepCheckEvent`).

- **Технические:**
  - DDD Архитектура (QuestStep принадлежит домену Quest, GeolocationService выделен в Shared).
  - Миграции PostgreSQL и обновление init-db.sql.
  - Сохранение гибкости структуры базы данных (без каскадного удаления и жестких FK).

## IMPLEMENTATION
- **Database & Entities:** Создана новая таблица `quest_steps` с INTEGER id, добавлены поля `type` в таблицу `quests` и `current_step_number` в таблицу `user_quest_progress`. Созданы соответствующие Doctrine сущности. Обновлен файл инициализации БД.
- **Shared Geo Services:** Создан модуль `Shared/Geo` с сервисом `GeolocationService`, реализующим расчет дистанции (Haversine) и проверку нахождения в радиусе.
- **Domain & Services:** 
  - Разработан `QuestStepRepositoryInterface` и его Doctrine-имплементация.
  - Добавлен `QuestStepService` для безопасного получения шагов (с проверкой прав доступа).
  - Модифицирован `UserProgressService`: добавлены методы автоматической установки первого шага при старте и сложная логика проверки локации (`checkQuestStep`), генерации Event Sourcing событий, а также автозавершения квеста (`complete()`), если шаг был последним.
- **API Endpoints:** Реализован контроллер `QuestStepController` и дополнен `UserProgressController`. 

## TESTING
- **Unit тесты:** Покрыт сервис геолокации `GeolocationService` на корректность расчета дистанции и валидации в граничных зонах. 
- **Integration & DB-dependent тесты:** Был разработан план для тестирования `QuestStepService` и `UserProgressService` (логика автоматического завершения, 403, 404, 422 ошибки). Из-за инфраструктурных ограничений (необходимость Docker окружения) интеграционные тесты были отложены до момента полноценного развертывания CI-pipeline, хотя код полностью подготовлен для них.

## LESSONS LEARNED
- Выделение логики геолокации в слой `Shared` с самого начала — удачное решение, так как эта логика переиспользуется несколькими доменами.
- Включение `current_step_number` прямо в агрегат `UserQuestProgress` оказалось проще и надежнее, чем создание дополнительной таблицы, особенно для `LINEAR` типа квестов.
- Event Sourcing (CQST-010) доказал свою масштабируемость: добавить новое событие `QuestStepCheckEvent` и записать дистанцию оказалось тривиальной задачей.
- Использование Integer Auto Increment ID вместо UUID для сущности QuestStep уменьшило оверхед базы данных, что оптимально для упорядоченных шагов.

## REFERENCES
- **Tasks File:** `memory-bank/tasks.md`
- **Reflection:** `memory-bank/reflection/reflection-CQST-012.md`
- **Design/Creative Document:** `memory-bank/creative/creative-flutter-client.md`
- **Event Sourcing Pattern:** `memory-bank/archive/archive-CQST-010-20251228.md`