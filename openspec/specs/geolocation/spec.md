# Geolocation Specification

## Purpose
Определяет логику работы со справочником городов и геолокационными данными (вычисление расстояний, проверка радиуса) в проекте CityQuest.

## Requirements

### Requirement: City List Retrieval
Система ДОЛЖНА предоставлять публичный эндпоинт для получения списка всех доступных городов. Список формируется на основе статической конфигурации приложения.

#### Scenario: Successfully fetching the list of cities
- **WHEN** клиент выполняет GET-запрос к `/api/cities`
- **THEN** система возвращает статус 200 OK
- **AND** в теле ответа содержится массив `data` с объектами городов (`key`, `name`) и объект `meta` (`total`, `count`)
- **AND** список городов отсортирован по алфавиту (по полю `name`)

### Requirement: Optional Authentication for Cities
Эндпоинт списка городов ДОЛЖЕН быть публичным и не требовать JWT токена для доступа.

#### Scenario: Anonymous user fetches cities
- **WHEN** неаутентифицированный клиент обращается к `/api/cities`
- **THEN** система успешно отдает список городов без ошибки 401 Unauthorized

### Requirement: Distance Calculation
Система ДОЛЖНА уметь вычислять расстояние в метрах между двумя географическими координатами (широта и долгота) с использованием формулы гаверсинуса (Haversine formula).

#### Scenario: Calculating distance between two known points
- **WHEN** система запрашивает расстояние между двумя различными точками
- **THEN** возвращается корректная дистанция в метрах с учетом радиуса Земли (6371000 м)
- **TESTS**:
  - Backend: `GeolocationServiceTest::testCalculateDistanceBetweenKnownPoints`

#### Scenario: Calculating distance to the same point
- **WHEN** система запрашивает расстояние между идентичными координатами
- **THEN** возвращается 0.0 метров
- **TESTS**:
  - Backend: `GeolocationServiceTest::testCalculateDistanceToSamePoint`

### Requirement: Radius Checking
Система ДОЛЖНА уметь проверять, находятся ли заданные координаты пользователя в пределах определенного радиуса от целевой точки (включая границу радиуса).

#### Scenario: User is within target radius
- **WHEN** расстояние между координатами пользователя и точкой меньше заданного радиуса
- **THEN** система возвращает `true`
- **TESTS**:
  - Backend: `GeolocationServiceTest::testIsWithinRadiusTrue`

#### Scenario: User is outside target radius
- **WHEN** расстояние превышает заданный радиус
- **THEN** система возвращает `false`
- **TESTS**:
  - Backend: `GeolocationServiceTest::testIsWithinRadiusFalse`

#### Scenario: User is exactly at the radius boundary
- **WHEN** расстояние равно заданному радиусу
- **THEN** система возвращает `true`
- **TESTS**:
  - Backend: `GeolocationServiceTest::testIsWithinRadiusBoundary`
