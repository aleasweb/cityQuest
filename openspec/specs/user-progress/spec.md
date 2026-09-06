# UserProgress Specification

## Purpose
Определяет механизмы отслеживания прогресса пользователей в квестах, включая управление статусами, проверку геопозиции на шагах и сбор истории через Event Sourcing.

## Requirements

### Requirement: Управление жизненным циклом квеста
Система ДОЛЖНА позволять пользователю начинать, ставить на паузу, возобновлять, завершать и отменять квесты. У пользователя может быть только один активный квест одновременно.

#### Scenario: Start a new quest
- **WHEN** пользователь начинает новый квест
- **THEN** система создает `UserQuestProgress` со статусом `active`, устанавливает первый шаг и генерирует `QuestStartedEvent`
- **TESTS**:
  - Backend: `UserProgressControllerTest::testStartQuest`
  - Backend: `UserProgressServiceTest::testStartQuest`

#### Scenario: Start a quest when another is active
- **WHEN** пользователь пытается начать квест, имея другой активный квест
- **THEN** система возвращает ошибку 409 Conflict (`ActiveQuestExistsException`)
- **TESTS**:
  - Backend: `UserProgressControllerTest::testStartQuestConflict`
  - Backend: `UserProgressServiceTest::testStartQuestThrowsWhenActiveExists`

#### Scenario: Pause an active quest
- **WHEN** пользователь ставит активный квест на паузу
- **THEN** статус меняется на `paused` и генерируется `QuestPausedEvent`
- **TESTS**:
  - Backend: `UserProgressControllerTest::testPauseQuest`

#### Scenario: Resume a paused quest
- **WHEN** пользователь начинает квест, который ранее был поставлен на паузу
- **THEN** статус меняется на `active` и генерируется `QuestResumedEvent`

#### Scenario: Abandon a quest
- **WHEN** пользователь отменяет квест (DELETE запрос)
- **THEN** статус меняется на `new`, генерируется `QuestAbandonedEvent`, и прогресс удаляется из активных

### Requirement: Проверка геопозиции на шагах квеста
Система ДОЛЖНА проверять координаты пользователя относительно текущего шага квеста и автоматически переводить на следующий шаг или завершать квест.

#### Scenario: Successful step check
- **WHEN** пользователь отправляет координаты, находящиеся в радиусе текущего шага
- **THEN** система регистрирует прохождение шага (`QuestStepCheckEvent`), возвращает `success: true` и номер следующего шага
- **TESTS**:
  - Backend: `UserProgressControllerTest::testCheckQuestStepSuccess`
  - Backend: `UserProgressServiceTest::testCheckQuestStepSuccess`

#### Scenario: Failed step check (out of radius)
- **WHEN** пользователь отправляет координаты вне радиуса текущего шага
- **THEN** система возвращает `success: false` с указанием текущей дистанции до точки и ошибкой 422 Unprocessable Entity
- **TESTS**:
  - Backend: `UserProgressControllerTest::testCheckQuestStepOutOfRadius`

#### Scenario: Completing the last step
- **WHEN** пользователь успешно проходит последний шаг квеста
- **THEN** система автоматически меняет статус квеста на `completed` и генерирует `QuestCompletedEvent`
- **TESTS**:
  - Backend: `UserProgressServiceTest::testCheckLastQuestStepCompletesQuest`

### Requirement: Получение списка прогресса
Система ДОЛЖНА предоставлять пользователю список его начатых, завершенных и приостановленных квестов с метаданными.

#### Scenario: Get user progress list
- **WHEN** пользователь запрашивает свой прогресс
- **THEN** система возвращает список квестов с их статусами, а также метаинформацию (total, completed, in_progress, paused, liked)
- **TESTS**:
  - Backend: `UserProgressControllerTest::testGetUserProgress`

#### Scenario: Get filtered user progress
- **WHEN** пользователь запрашивает прогресс с фильтром `?status=active`
- **THEN** система возвращает только активный квест
