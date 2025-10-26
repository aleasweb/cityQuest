# Информация о Postman коллекции

## 📋 Метаданные

- **Название:** CityQuest API
- **Версия:** 1.0.1
- **Дата создания:** 2025-10-25
- **Последнее обновление:** 2025-10-26
- **Формат:** Postman Collection v2.1.0
- **Всего endpoints:** 7
- **Всего тестов:** 40+ автоматических проверок

## 📊 Статистика

### Endpoints по категориям

| Категория | Количество | Методы |
|-----------|------------|--------|
| Authentication | 3 | POST |
| User Profile | 3 | GET, PATCH |
| Health Check | 1 | GET |
| **Всего** | **7** | - |

### Покрытие тестами

| Endpoint | Автотесты | Примеры ответов |
|----------|-----------|-----------------|
| Register | 4 теста | 3 примера |
| Login | 4 теста | 2 примера |
| Logout | 3 теста | 1 пример |
| Get My Profile | 4 теста | 2 примера |
| Get Public Profile | 4 теста | 2 примера |
| Update Profile | 4 теста | 4 примера |
| Health Check | 3 теста | 1 пример |
| **Всего** | **26+ тестов** | **15 примеров** |

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

**Environment-level (Production):**
- `base_url` - https://api.cityquest.com
- `user_email` - (empty, fill manually)
- `user_password` - (empty, secret)
- `user_username` - (empty, fill manually)
- `jwt_token` - (auto-managed)

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
