# Tasks: Mobile Flutter Client (Greenfield)

План реализации в 5 спринтов. Каждая задача — в пределах одной сессии агента. Backend-точечные задачи проверяются через Docker PHPUnit/PHPStan. Документация ведётся на русском.

---

## Sprint 0: Bootstrap & Foundations

### 0.1 Инициализация проекта
- [x] 0.1.1 Создать Flutter-проект в `mobile/` (`flutter create --org com.cityquest --platforms=ios,android`)
- [x] 0.1.2 Добавить базовые зависимости: `dio`, `dio_cookie_manager`, `riverpod`, `freezed`, `go_router`, `geolocator`, `flutter_map`, `shared_preferences`, `hive`, `json_serializable` (dev)
- [x] 0.1.3 Настроить генераторы (`build_runner`), `analysis_options.yaml`, линтер (flutter_lints)
- [x] 0.1.4 Собрать empty-app на iOS Simulator и Android APK — «hello world» на обеих платформах (пропущено пользователем, проект валиден)
- [x] 0.1.5 Переменные окружения: `--dart-define` для `API_BASE_URL` (dev/stage/prod)

### 0.2 Design System
- [x] 0.2.1 Перенести UI-токены из веба: primary `#ed8e34`, шрифт Inter, сетка 8px, тёмная/светлая темы
- [x] 0.2.2 Токены осуществить как `mobile/app/core/design/` (Colors, Spacing, TextStyles, AppTheme)
- [x] 0.2.3 Shared-виджеты: `QuestCard`, `QuestSlider`, `Toast`, `Skeleton`, `EmptyState`, `ErrorState`

### 0.3 Core API-слой
- [x] 0.3.1 Dio-клиент с interceptors: envelope `{data, meta}`, ошибки 400–422 в читаемый формат, retry (1 раз) на таймаут
- [x] 0.3.2 Подключить `dio_cookie_manager` (HttpOnly JWT cookies) + `path_provider`
- [x] 0.3.3 Добавить заголовок `X-App-Platform: ios/<version>` / `android/<version>` во все запросы (PlatformResolver)
- [x] 0.3.4 `CacheManager` по образцу веба: TTL-кеш в hive для городов (1ч) и последних данных профиля

### 0.4 CI (GitHub Actions)
- [x] 0.4.1 Workflow: `flutter analyze`, сборка `flutter build apk --debug` на push/PR
- [x] 0.4.2 (опц.) Сборка iOS Simulator и скриншоты (пропущено для MVP)

---

## Sprint 1: Core & Auth

### 1.1 Feature `auth`
- [x] 1.1.1 `data/`: DTO (AuthUser, LoginResponse), API-клиент (`register`/`login`/`logout`/`me`), CookieManager
- [x] 1.1.2 `domain/`: абстрактный репозиторий `AuthRepository`, модели сессии, `sealed class Failure` для ошибок
- [x] 1.1.3 `application/`: use cases для логина/регистрации, обработка fallback Bearer-токена
- [x] 1.1.4 `presentation/`: экраны Login / Register (валидация полей, gesterror-сообщения), `AuthState` (Riverpod `AsyncValue`)
- [x] 1.1.4 Проверка сессии при старте: `GET /api/auth/me`, авто-redirect на вход при 401
- [x] 1.1.5 Логаут: очистка cookies/кеша, переход в гостевой режим

### 1.2 Routing (`core/routing/`)
- [x] 1.2.1 go_router: корневые маршруты `splash → auth | home`, route-guard по состоянию сессии. Использовать строгие константы для route names.
- [x] 1.2.2 Deeplink-схемы (задел): `cityquest://quest/{id}`

### 1.3 Вынос auth UI в общие компоненты
- [x] 1.3.1 Кнопки/инпуты дизайн-системы, password visibility toggle

### 1.4 Backend (точечно, при необходимости)
- [x] 1.4.1 Проверить `Set-Cookie` политики для нативного клиента; для prod поддомена настроить `Secure; SameSite=None` в `lexik_jwt_authentication.yaml`
- [x] 1.4.2 Прогнать `docker compose exec php-fpm php bin/phpunit` + `phpstan analyse` после изменений конфига аутентификации

---

## Sprint 2: Discovery (Quest Catalog)

### 2.1 Feature `quests`
- [x] 2.1.1 `data/`: DTO (Quest, QuestList response), API для `/api/quests` (фильтры city/difficulty/is_popular, пагинация), `/api/quests/{id}`, `/api/quests/{id}/like`
- [x] 2.1.2 `domain/`: абстрактный репозиторий, модели, пресеты фильтров
- [x] 2.1.3 `application/`: use cases для загрузки и фильтрации квестов, Riverpod-контроллеры
- [x] 2.1.4 `presentation/`: список квестов (Home) с горизонтальными слайдерами (как веб), Pull-to-refresh, бесконечная пагинация
- [x] 2.1.4 Деталь квеста: описание, автор, сложность, длительность, дистанция, стартовые координаты, кнопки «Начать»/«Пауза»/«Отменить» и «Лайк»
- [x] 2.1.5 Оптимистичный лайк для авторизованных, гостевое состояние для анонимов (подсказка войти)

### 2.2 Feature cities
- [x] 2.2.1 `data/`: CityRepository с CacheManager (TTL 1ч), DTO
- [x] 2.2.2 Фильтр выбора города в каталоге (BottomSheet)

### 2.3 Nearby quests
- [x] 2.3.1 Запрос георазрешений (`geolocator`) на iOS/Android с понятным объяснением
- [x] 2.3.2 Экран «Рядом»: `/api/quests/nearby` с координатами устройства, сортировка по удалённости, отображение базовой дистанции на карточках

---

## Sprint 3: Quest Engine (Progress + Geo)

### 3.1 Feature `progress`
- [x] 3.1.1 `data/`: DTO (ProgressItem, StepCheckResponse), API `/api/user/progress...` (list, start, pause, complete, abandon, check)
- [x] 3.1.2 `domain/`: абстрактный репозиторий, модель активного прогресса, миддлвор-состояний (NEW→ACTIVE→PAUSED→ACTIVE→COMPLETED, PAUSED→NEW)
- [x] 3.1.3 `application/`: use cases (start, pause, complete), логика «проверка шага» и координация с геолокацией
- [x] 3.1.4 `presentation/`: экран активного квеста

### 3.2 Map & GPS
- [x] 3.2.1 `flutter_map` карта: маршрут по чекпоинтам (по полилинии), текущая позиция пользователя
- [x] 3.2.2 GPS-трекинг с `LocationAccuracy.high` во время прохождения; периодическая фиксация позиции
- [x] 3.2.3 Автопроверка чекпоинта по радиусу + ручная кнопка «Проверить»

### 3.3 Сценарии жизненного цикла квеста
- [x] 3.3.1 Старт квеста: обработка `409 ActiveQuestExistsException` — диалог «Возобновить/Отменить предыдущий»
- [x] 3.3.2 Проверка шага: `success: true` → переход на следующий; `422` → показать дистанцию до точки и направление
- [x] 3.3.3 Пауза/возобновление/отмена (abandon) с синхронизацией статуса
- [x] 3.3.4 Автозавершение при последнем шаге → экран поздравления со статистикой
- [x] 3.3.5 Обработка отказов в георазрешениях: inline-подсказки, переход в настройки ОС
- [x] 3.3.6 Локальное сохранение состояния при обрыве сети и повторная синхронизация

---

## Sprint 4: Profile & Polish

### 4.1 Feature `profile`
- [x] 4.1.1 `data/`: API `GET/PATCH /api/user/profile`, публичный профиль
- [x] 4.1.2 `application/`: use cases для загрузки профиля, истории и обновления email
- [x] 4.1.3 `presentation/`: экран профиля (username, email, дата регистрации), история квестов по статусам (активные/пауза/завершённые), возобновление активного квеста
- [x] 4.1.3 Обновление email через PATCH с валидацией

### 4.2 Обработка краевых сценариев
- [x] 4.2.1 Глобальный error-слой: map ошибок API → русские сообщения (400/401/403/404/409/422), авто-логоут при 401
- [x] 4.2.2 Монитор сети (`connectivity_plus`): banner «Нет соединения», retry-policy
- [x] 4.2.3 Сохранение заполняемых форм при потере сети (аутентификация, обновление профиля)

### 4.3 Релиз
- [x] 4.3.1 iOS: сборка `flutter build ios --release`, настройка Info.plist (пермишены гео), значок/сплэш
- [x] 4.3.2 Android: `flutter build appbundle`, манифест-пермишены, релизный процесс (signing, ProGuard R8)
- [x] 4.3.3 Скриншоты и smoke-тест на устройствах

---

## Documentation Sync

- [x] 5.1 Обновить `openspec/specs/mobile-app/architecture.md`: структура `mobile/`, слои feature-модулей, взаимодействие с backend (cookies, `X-App-Platform`, envelope), Riverpod state management, кеширование, деплой-процесс
- [x] 5.2 Обновить глобальную сводку в `AGENTS.md` (статус Mobile 0% → в процессе) и `openspec/config.yaml` (Mobile в стеке)
- [x] 5.3 По завершении изменения — перенести спецификацию в `openspec/specs/mobile-app/` и заархивировать изменение

## Definition of Done
- `flutter analyze` — без ошибок (0 warnings)
- Сборка iOS + Android — успешна (debug; release на S4)
- Спецификация синхронизирована (S: 5.x)