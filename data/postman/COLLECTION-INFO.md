# Информация о Postman коллекции

## 📋 Метаданные

- **Название:** CityQuest API
- **Версия:** 1.1.0
- **Дата создания:** 2025-10-25
- **Последнее обновление:** 2025-11-29
- **Формат:** Postman Collection v2.1.0
- **Всего endpoints:** 15
- **Всего тестов:** 44+ автоматических проверок

## 📊 Статистика

### Endpoints по категориям

| Категория | Количество | Методы |
|-----------|------------|--------|
| Authentication | 3 | POST |
| User Profile | 3 | GET, PATCH |
| Quests | 4 | GET (list, nearby, by ID), POST (like) |
| User Progress | 4 | GET (progress), POST (start), PATCH (pause, complete) |
| Health Check | 1 | GET |
| **Всего** | **15** | - |

### Покрытие тестами

| Endpoint | Автотесты | Примеры ответов |
|----------|-----------|-----------------|
| Register | 4 теста | 3 примера |
| Login | 4 теста | 2 примера |
| Logout | 3 теста | 1 пример |
| Get My Profile | 4 теста | 2 примера |
| Get Public Profile | 4 теста | 2 примера |
| Update Profile | 4 теста | 4 примера |
| Get Quest by ID | 4 теста | 3 примера |
| Get Quest List | - | - |
| Get Nearby Quests | - | - |
| Toggle Quest Like | - | - |
| Get User Progress | - | - |
| Start Quest | - | - |
| Pause Quest | - | - |
| Complete Quest | - | - |
| Health Check | 3 теста | 1 пример |
| **Всего** | **30+ тестов** | **18 примеров** |

### Глобальные тесты
- Response time check (все запросы)
- Content-Type validation (все запросы)

## 🔐 Security Features

✅ JWT token автоматическое управление  
✅ Token хранится как secret переменная  
✅ Автоочистка token при logout  
✅ Production environment без credentials в Git  
✅ Bearer token authentication настроена глобально

## 🎯 Use Cases

### 1. Ручное тестирование API
- Быстрая проверка функциональности
- Отладка specific endpoints
- Тестирование edge cases

### 2. Автоматизированное тестирование
- Collection Runner для regression tests
- Newman CLI для CI/CD
- Scheduled tests через Postman Monitor

### 3. Документация API
- Примеры реальных запросов
- Описания endpoints
- Examples успешных и ошибочных ответов

### 4. Onboarding новых разработчиков
- Готовая коллекция для старта
- Все endpoints документированы
- Примеры использования включены

## 🛠️ Технические детали

### Переменные

**Collection-level:**
- `base_url` - базовый URL API

**Environment-level (Local):**
- `base_url` - http://cityquest.test
- `user_email` - test@example.com
- `user_password` - testPassword123
- `user_username` - testuser
- `jwt_token` - (auto-managed)
- `quest_id` - 550e8400-e29b-41d4-a716-446655440000

**Environment-level (Production):**
- `base_url` - https://api.cityquest.com
- `user_email` - (empty, fill manually)
- `user_password` - (empty, secret)
- `user_username` - (empty, fill manually)
- `jwt_token` - (auto-managed)
- `quest_id` - (empty, fill manually)

### Scripts

**Pre-request scripts:**
- Логирование URL запроса

**Test scripts:**
- Status code validation
- Response structure validation
- JWT token extraction and storage
- Response time checks
- Content-Type checks
- UUID format validation
- Business logic validation

### Authentication

**Type:** Bearer Token  
**Location:** Authorization header  
**Format:** `Bearer {{jwt_token}}`  
**Management:** Автоматическое (через test scripts)

**Login Credentials:**
- **Field:** `username` (не email!)
- **Variable:** `{{user_username}}`
- **Note:** Авторизация происходит по username, а не по email

## 📈 Версионирование

### v1.0.0 (2025-10-25) - Initial Release
- ✅ Authentication endpoints (Register, Login, Logout)
- ✅ Health Check endpoint
- ✅ Автоматические тесты для всех endpoints
- ✅ Примеры успешных и ошибочных ответов
- ✅ Local и Production environments
- ✅ JWT token auto-management
- ✅ Comprehensive README

### Planned for v1.1.0
- User Profile endpoints (GET/PATCH/DELETE /api/users/me)
- Extended test coverage
- Mock server examples

### Planned for v2.0.0
- Quest Management endpoints
- Achievement endpoints
- Location services endpoints

## 🔄 Обновление коллекции

### При добавлении новых endpoints:

1. Добавить request в соответствующую папку
2. Добавить описание endpoint
3. Добавить test scripts
4. Добавить примеры ответов (success + errors)
5. Обновить README.md
6. Обновить COLLECTION-INFO.md
7. Увеличить версию в info.version

### Checklist для нового endpoint:
- [ ] Request настроен (method, URL, headers, body)
- [ ] Описание добавлено (description)
- [ ] Test scripts добавлены (минимум 3 теста)
- [ ] Примеры ответов добавлены (success + error cases)
- [ ] Переменные используются (не hardcode)
- [ ] Authentication настроен (если требуется)
- [ ] README обновлен
- [ ] Версия обновлена

## 📦 Файлы в директории

```
data/postman/
├── CityQuest-API.postman_collection.json      # Основная коллекция
├── CityQuest-Local.postman_environment.json   # Local environment
├── CityQuest-Production.postman_environment.json  # Production environment
├── README.md                                   # Инструкция по использованию
├── COLLECTION-INFO.md                         # Этот файл
└── .gitignore                                 # Git ignore rules
```

## 🎓 Best Practices

### При работе с коллекцией:

1. **Используйте правильное environment**
   - Local для разработки
   - Production для staging/production тестов

2. **Не коммитьте credentials**
   - Production environment в Git без паролей
   - Используйте secret type для sensitive data

3. **Запускайте тесты регулярно**
   - После каждого изменения API
   - Перед деплоем в production
   - В CI/CD pipeline

4. **Обновляйте документацию**
   - При добавлении endpoints
   - При изменении структуры ответов
   - При изменении бизнес-логики

5. **Используйте примеры**
   - Добавляйте реальные примеры ответов
   - Включайте edge cases
   - Документируйте все коды ошибок

## 📞 Support & Contributing

### Вопросы и предложения
- Создайте Issue в репозитории проекта
- Опишите проблему или предложение
- Приложите скриншоты если необходимо

### Contributing
1. Fork репозитория
2. Создайте feature branch
3. Добавьте/измените endpoints
4. Обновите тесты и документацию
5. Создайте Pull Request

## 📋 Changelog

### v1.1.0 (2025-11-29)
**Новые возможности:**
- ✅ **Quest Lists API** - получение списков квестов с фильтрацией и сортировкой
- ✅ **Geosearch API** - поиск квестов по геолокации (Haversine formula)
- ✅ **Quest Likes** - система лайков квестов (toggle mechanism)
- ✅ **User Progress API** - полное управление прогрессом пользователя
- ✅ **Quest Status Management** - старт/пауза/завершение квестов
- ✅ 7 новых endpoints (+4 Quest, +4 User Progress, -1 перемещен)
- ✅ Бизнес-правило: только 1 активный квест одновременно (409 Conflict)

**API Endpoints:**

*Публичные (без JWT):*
- GET /api/quests - список квестов (фильтры: city, difficulty, isPopular | сортировка: created, likes | пагинация: limit, offset)
- GET /api/quests/nearby - геопоиск (параметры: lat, lng, radius)

*Приватные (требуют JWT):*
- POST /api/quests/{id}/like - toggle лайк
- GET /api/user/progress - прогресс пользователя (фильтры: status, liked)
- POST /api/user/progress/{questId}/start - начать/возобновить квест
- PATCH /api/user/progress/{questId}/pause - поставить на паузу
- PATCH /api/user/progress/{questId}/complete - завершить квест

**Технические улучшения:**
- Database migration: таблица `user_quest_progress` (status: active/paused/completed)
- Геолокация: добавлены поля latitude/longitude в таблицу quests
- Domain: новый UserProgress domain (Entity, ValueObject, Exceptions, Repository)
- Quest Domain: расширен для списков и геопоиска
- PHP 8.1 Enum: QuestStatus для type-safe управления статусами
- 3 новых Application Services
- 75 tests, 264 assertions - ALL PASSED ✅

**Архитектура:**
- DDD структура для UserProgress домена
- Repository pattern с фильтрацией и геопоиском
- Domain exceptions для бизнес-правил (ActiveQuestExistsException, InvalidQuestStatusException, ProgressNotFoundException)
- Comprehensive тестирование (unit + integration)

### v1.0.2 (2025-11-29)
**Новые возможности:**
- ✅ **Quest Data API** - базовый endpoint для получения данных квестов
- ✅ **Get Quest by ID** - получение квеста по UUID (публичный endpoint)
- ✅ Добавлена переменная окружения: `quest_id`
- ✅ 4 новых автоматических теста
- ✅ 3 новых примера ответов (success, not found, invalid UUID)

**Технические улучшения:**
- Публичный доступ к Quest API (без JWT аутентификации)
- UUID валидация с корректными error messages
- Полная обработка ошибок (400, 404, 500)
- Автоматические тесты для всех сценариев

**Архитектура:**
- DDD структура для Quest домена
- Repository pattern с интерфейсами
- Domain exceptions для бизнес-логики
- Comprehensive тестирование (unit + integration)

### v1.0.1 (2025-10-26)
**Новые возможности:**
- ✅ **User Profile Management** - полное управление профилями пользователей
- ✅ **Get My Profile** - получение собственного профиля с email
- ✅ **Get Public Profile** - просмотр публичных профилей других пользователей
- ✅ **Update Profile** - обновление email с валидацией уникальности
- ✅ Добавлены переменные окружения: `public_username`, `new_user_email`
- ✅ 12 новых автоматических тестов
- ✅ 8 новых примеров ответов

**Технические улучшения:**
- Публичные endpoints не требуют аутентификации
- Приватные данные (email) скрыты в публичных профилях
- Полная валидация и обработка ошибок
- Автоматические тесты для всех сценариев

**Исправления:**
- ✅ **Login по username** - исправлена авторизация с email на username
- ✅ Обновлены примеры запросов в коллекции
- ✅ Обновлена документация

### v1.0.0 (2025-10-25)
**Базовая функциональность:**
- Authentication endpoints (Register, Login, Logout)
- Health Check endpoint
- JWT token management
- Автоматические тесты и примеры

## 🔗 Ссылки

- **Project Repository:** (to be added)
- **API Documentation:** `memory-bank/mvp-spec.md`
- **Reflection Document:** `memory-bank/reflection/reflection-CQST-001.md`
- **Test Coverage Report:** `memory-bank/test-coverage-report.md`
- **Postman Documentation:** https://learning.postman.com/

---

**Создано:** 2025-10-25  
**Task:** CQST-001 - Authentication System  
**Автор:** CityQuest Development Team
