# Technical Context - CityQuest

> **Технический контекст и детали реализации**

## 🛠️ Технологический стек

### Backend
- **Framework:** Symfony 6.4+
- **PHP:** 8.3
- **Database:** PostgreSQL 16
- **ORM:** Doctrine ORM
- **Authentication:** Symfony Security Bundle
- **Testing:** PHPUnit 10+
- **Code Quality:** PHPStan, PHP-CS-Fixer

### Frontend
- **Framework:** React 18
- **Build Tool:** Vite
- **Language:** TypeScript 5+
- **Styling:** Tailwind CSS
- **Routing:** React Router 6
- **State Management:** Zustand
- **Maps:** React-Leaflet (OpenStreetMap)
- **i18n:** i18next

### Infrastructure
- **Containerization:** Docker + Docker Compose
- **Web Server:** Nginx
- **PHP Runtime:** PHP-FPM 8.3
- **Database:** PostgreSQL 16

## 🔧 Конфигурация окружения

### Docker Services
```yaml
services:
  nginx:      # Веб-сервер
  php-fpm:    # PHP runtime
  postgres:   # База данных
```

### Порты
- `80` - Nginx (HTTP)
- `9000` - PHP-FPM
- `5432` - PostgreSQL

## 📦 Зависимости

### Backend (composer.json)
Основные:
- `symfony/framework-bundle`
- `symfony/security-bundle`
- `doctrine/orm`
- `doctrine/doctrine-bundle`
- `doctrine/doctrine-migrations-bundle`
- `monolog/monolog`
- `twig/twig`

Dev:
- `phpunit/phpunit`
- `phpstan/phpstan`
- `friendsofphp/php-cs-fixer`
- `symfony/web-profiler-bundle`

### Frontend (package.json)
Основные:
- `react`
- `react-dom`
- `react-router-dom`
- `zustand`
- `leaflet`
- `react-leaflet`
- `i18next`

Dev:
- `vite`
- `typescript`
- `eslint`
- `tailwindcss`
- `postcss`

## 🗄️ Структура базы данных

### Основные таблицы

1. **users**
   - id, email, password, username
   - Хранение пользователей

2. **quests**
   - id, title, description, city, difficulty
   - duration_minutes, distance_km, image_url
   - author, likes_count, is_popular
   - Хранение квестов

3. **quest_steps**
   - id, quest_id, title, text
   - image_url, audio_url, video_url
   - lat, lng, radius
   - Этапы квестов

4. **user_quest_progress**
   - id, user_id, quest_id
   - is_completed, is_liked, completed_at
   - Прогресс прохождения

## 🌐 API Endpoints

### Аутентификация
- `POST /api/auth/register` - Регистрация
- `POST /api/auth/login` - Вход
- `POST /api/auth/logout` - Выход

### Квесты (публичные)
- `GET /api/quests` - Список с фильтрами
- `GET /api/quests/nearby` - Поиск рядом
- `GET /api/quests/{id}` - Детали

### Квесты (авторизованные)
- `POST /api/quests/{id}/like` - Лайк/дизлайк

### Прогресс
- `GET /api/user/progress` - Прогресс пользователя
- `POST /api/user/progress/{questId}/start` - Начать
- `PATCH /api/user/progress/{questId}/complete` - Завершить

## 🔐 Безопасность

### Аутентификация
- JWT токены для API
- Session-based для web
- Bcrypt для хеширования паролей

### CORS
- Настроен для frontend домена
- Whitelist доменов

### Валидация
- Symfony Validator для входных данных
- Sanitization всех пользовательских вводов

## 🧪 Тестирование

### Стратегия
- Unit tests для доменной логики
- Integration tests для API
- Минимум 80% code coverage

### Команды
```bash
# Backend tests
docker-compose exec php-fpm php bin/phpunit

# Static analysis
docker-compose exec php-fpm vendor/bin/phpstan analyse

# Code style
docker-compose exec php-fpm vendor/bin/php-cs-fixer fix
```

## 📝 Команды разработки

### Backend
```bash
# Установка зависимостей
make install

# Запуск контейнеров
docker-compose up -d

# Остановка
docker-compose down

# Логи
docker-compose logs -f php-fpm

# Миграции
docker-compose exec php-fpm php bin/console doctrine:migrations:migrate
```

### Frontend
```bash
# Установка
cd frontend/web && npm install

# Dev сервер
npm run dev

# Build
npm run build

# Preview
npm run preview
```

## 🌍 Окружения

### Development
- Debug mode включен
- Web Profiler активен
- Подробные ошибки

### Production (планируется)
- Debug mode выключен
- Оптимизированные ассеты
- Минимальная информация об ошибках
- HTTPS обязателен

---

**Последнее обновление:** 2025-10-25
