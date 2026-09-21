# CI/CD Backend + Frontend — Production Deployment

## Обзор архитектуры

```
Client → Nginx (порт 80/443)
           ├── /api/*      → PHP-FPM (Symfony 6.4)
           ├── /s3/*       → Статика квестов
           └── /*          → React SPA (dist/)
         PostgreSQL 16
```

Всё разворачивается через Docker Compose. Nginx раздаёт собранный React SPA и проксирует `/api/` в PHP-FPM.

## Сервисы Docker Compose

| Сервис | Образ | Роль |
|--------|-------|------|
| `php-fpm` | `php:8.3-fpm` (custom) | Symfony API |
| `nginx` | `nginx:1.25-alpine` (custom) | Reverse proxy + SPA hosting |
| `proxy` | `jwilder/nginx-proxy:1.5-alpine` | Virtual host routing (dev) |
| `db` | `postgres:16-alpine` | База данных |
| `pgadmin` | `dpage/pgadmin4:8` | Админка БД (dev only) |

## Пререквизиты

- Docker ≥ 24, Docker Compose V2
- Node 20+ (для локальной сборки фронтенда, или используйте Docker-сборку)
- Домен/IP сервера, DNS A-запись
- SSL-сертификат (Let's Encrypt / Certbot)

## Переменные окружения

### Корневой `.env` (Docker Compose)

```env
PHP_VERSION=8.3
XDEBUG_VERSION=3.3.1

NGINX_VIRTUAL_HOST=cityquest.ru
NGINX_PORT=80

POSTGRES_VERSION=16
POSTGRES_PORT=5432
POSTGRES_DB=cityquest
POSTGRES_USER=<strong_user>
POSTGRES_PASSWORD=<strong_password>
```

### Backend `backend/.env.local` (НЕ коммитить)

```env
APP_ENV=prod
APP_SECRET=<random_32hex>

DATABASE_URL=postgresql://<user>:<password>@db:5432/cityquest?serverVersion=16&charset=utf8

JWT_PASSPHRASE=<random_passphrase>

CORS_ALLOW_ORIGIN='^https://cityquest\.ru$'
```

## Шаг 1: Подготовка сервера

```bash
# Установка Docker
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER

# Клонирование
git clone <repo_url> /opt/cityquest
cd /opt/cityquest
```

## Шаг 2: Генерация JWT-ключей

```bash
mkdir -p backend/config/jwt
openssl genpkey -out backend/config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in backend/config/jwt/private.pem -out backend/config/jwt/public.pem -pubout
chmod 644 backend/config/jwt/private.pem backend/config/jwt/public.pem
```

Парольная фраза → `JWT_PASSPHRASE` в `backend/.env.local`.

## Шаг 3: Сборка фронтенда

### Вариант A — Через Docker (без Node на сервере)

```bash
./frontend/build-frontend-docker.sh
```

Скрипт собирает `frontend/web/dist/` в изолированном контейнере Node 20, затем перезапускает nginx.

### Вариант B — Локально

```bash
cd frontend/web
npm install && npm run build
cd ../..
```

Результат: `frontend/web/dist/` монтируется в nginx как `/app/frontend/dist:ro`.

## Шаг 4: Запуск

```bash
# Создать .env файлы (см. раздел «Переменные окружения»)
cp .env .env          # отредактировать production-значения
cp backend/.env backend/.env.local  # APP_ENV=prod, сильные пароли

# Собрать и запустить
docker compose build
docker compose up -d

# Установить PHP-зависимости (без dev)
docker compose exec php-fpm composer install --no-dev --optimize-autoloader

# Прогреть кэш Symfony
docker compose exec php-fpm php bin/console cache:clear --env=prod
docker compose exec php-fpm php bin/console cache:warmup --env=prod

# Миграции
docker compose exec php-fpm php bin/console doctrine:migrations:migrate --no-interaction
```

> **Первый запуск:** БД инициализируется автоматически из `data/init-db/cityquest.sql` (Docker entrypoint). Миграции нужны только для последующих обновлений схемы.

## Шаг 5: SSL (HTTPS)

Для production **обязателен** HTTPS — JWT-куки передаются с `secure: true`.

### Certbot + Nginx

```bash
# Установить Certbot
sudo apt install certbot python3-certbot-nginx

# Получить сертификат
sudo certbot --nginx -d cityquest.ru

# Автообновление
sudo systemctl enable certbot.timer
```

Либо настроить SSL-терминацию на уровне `jwilder/nginx-proxy` через `docker-letsencrypt-nginx-proxy-companion`.

## Шаг 6: Проверка

```bash
# Health check
curl -s https://cityquest.ru/api/quests | head

# Логи
docker compose logs -f nginx php-fpm

# Статус
docker compose ps
```

## Production-отличия от dev

| Аспект | Dev | Production |
|--------|-----|------------|
| `APP_ENV` | `dev` | `prod` |
| Xdebug | Включён | **Отключить** (убрать из Dockerfile или переменной) |
| JWT cookie `secure` | `false` | `true` (config `prod/lexik_jwt_authentication.yaml`) |
| JWT cookie `samesite` | `strict` | `none` (для cross-origin) |
| CORS origin | `localhost\|cityquest.test` | `^https://cityquest\.ru$` |
| Composer | `install` | `install --no-dev --optimize-autoloader` |
| Doctrine proxy | auto-generate | pre-generated (`auto_generate_proxy_classes: false`) |
| Doctrine cache | нет | `cache.app` + `cache.system` pools |
| pgAdmin | Включён | **Убрать** из compose или отдельный compose-файл |
| proxy (nginx-proxy) | Включён | Опционально (если один домен — не нужен) |

## Обновление (деплой новой версии)

```bash
cd /opt/cityquest
git pull origin main

# Пересобрать фронтенд
./frontend/build-frontend-docker.sh

# Обновить backend
docker compose exec php-fpm composer install --no-dev --optimize-autoloader
docker compose exec php-fpm php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec php-fpm php bin/console cache:clear --env=prod

# Перезапуск (если менялись Docker-файлы)
docker compose up -d --build
```

Быстрый деплой (только фронтенд + рестарт):

```bash
make deploy  # = frontend-build + restart
```

## Безопасность

- **Никогда** не коммитить `backend/.env.local`, JWT-ключи, production-пароли
- `APP_SECRET` — уникальный для каждого окружения
- Отключить Xdebug в production
- Закрыть порт PostgreSQL (5432) от внешнего доступа
- Закрыть/удалить pgAdmin в production
- Настроить firewall: открыты только 80, 443, 22 (SSH)

## Рекомендуемая production-структура compose

Для production рекомендуется создать `compose.prod.yaml` (override), исключающий dev-сервисы:

```yaml
# compose.prod.yaml
services:
  php-fpm:
    build:
      args:
        - XDEBUG_VERSION=  # не устанавливать xdebug
    environment:
      APP_ENV: prod

  db:
    ports: !override []  # не пробрасывать порт наружу

  proxy:
    profiles: [dev]  # не запускать в prod

  pgadmin:
    profiles: [dev]  # не запускать в prod
```

Запуск: `docker compose -f compose.yaml -f compose.prod.yaml up -d`
