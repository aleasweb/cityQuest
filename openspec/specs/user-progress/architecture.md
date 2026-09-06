# Архитектура домена UserProgress

Домен `user-progress` отвечает за отслеживание прогресса прохождения квестов пользователями, включая старт, паузу, возобновление, отмену и прохождение шагов с учетом геопозиции.

## 1. Обзор архитектуры (DDD)

Домен реализован в соответствии с принципами Domain-Driven Design (DDD) и располагается в `project/src/UserProgress/`.

**Слои:**
- **Domain:** Содержит агрегат `UserQuestProgress`, Value Object `QuestStatus`, доменные события (наследуют `AbstractUserQuestProgressEvent`), интерфейсы репозиториев (`UserQuestProgressRepositoryInterface`, `ProgressEventStoreInterface`) и исключения (`ProgressNotFoundException`, `ActiveQuestExistsException`, `InvalidQuestStatusException`).
- **Application:** Сервис `UserProgressService`, оркестрирующий бизнес-логику, проверяющий геопозицию (через `GeolocationService`) и управляющий сохранением событий.
- **Infrastructure:** Реализации репозиториев для работы с базой данных через Doctrine (`DoctrineUserQuestProgressRepository`, `DoctrineProgressEventStore`).
- **Presentation:** REST-контроллер `UserProgressController`.

## 2. Event Sourcing

Прогресс пользователя использует паттерн Event Sourcing для надежного хранения истории изменений и аналитики.

- Агрегат `UserQuestProgress` реализует трейт `RecordsEvents`.
- Все изменения состояния (старт, пауза, прохождение шага, завершение) генерируют соответствующие события (`QuestStartedEvent`, `QuestPausedEvent`, `QuestResumedEvent`, `QuestCompletedEvent`, `QuestAbandonedEvent`, `QuestStepCheckEvent`).
- События сохраняются в отдельную таблицу (через `ProgressEventStoreInterface`) в режиме append-only.
- Метод `mutate` в агрегате применяет события для изменения внутреннего состояния (статус, текущий шаг, время обновления).

## 3. Жизненный цикл квеста (QuestStatus)

Управление состояниями строго контролируется через конечный автомат в `QuestStatus::canTransitionTo`.

Допустимые переходы:
- **NEW** -> **ACTIVE** (старт нового квеста)
- **ACTIVE** -> **PAUSED** (постановка на паузу)
- **ACTIVE** -> **COMPLETED** (успешное завершение всех шагов)
- **PAUSED** -> **NEW** (отмена квеста - abandon)
- **PAUSED** -> **ACTIVE** (возобновление)

У пользователя может быть **только один** квест в статусе `ACTIVE` одновременно.

## 4. Геочеки (Quest Steps)

Прохождение квеста базируется на физическом перемещении пользователя по контрольным точкам (шагам).

- Проверка нахождения пользователя в радиусе контрольной точки осуществляется через `GeolocationService`.
- При успешном чеке:
  - Генерируется `QuestStepCheckEvent`.
  - Если это последний шаг, квест автоматически переходит в статус `COMPLETED`.
  - Иначе обновляется `currentStepNumber` на следующий активный шаг.
- При неудачном чеке (пользователь вне радиуса) возвращается ошибка с указанием текущей дистанции до точки.

## 5. API Endpoints

Все эндпоинты защищены и требуют авторизации (JWT в HttpOnly cookie).

- `GET /api/user/progress` — получение списка прогресса (поддерживает фильтр `?status=active|paused|completed`). Возвращает данные квестов и метаинформацию (счетчики).
- `POST /api/user/progress/{questId}/start` — старт нового квеста или возобновление приостановленного.
- `PATCH /api/user/progress/{questId}/pause` — постановка активного квеста на паузу.
- `PATCH /api/user/progress/{questId}/complete` — ручное завершение квеста.
- `POST /api/user/progress/{questId}/check` — проверка геопозиции на текущем шаге. Принимает JSON: `{"latitude": float, "longitude": float}`.
- `DELETE /api/user/progress/{questId}` — отмена квеста (abandon) и удаление прогресса.

## 6. Связь с другими доменами

- **Quest:** `UserProgressService` обращается к `QuestRepositoryInterface` и `QuestStepRepositoryInterface` для получения данных о квестах и их шагах. Также используется `QuestLikeService` для обогащения ответов статусами лайков.
- **Shared/Geo:** Использование `GeolocationService` для вычисления дистанции и проверки попадания в радиус (haversine formula).
- **Platform:** Использование `PlatformResolver` для добавления информации о платформе (web/ios/android) в доменные события.
