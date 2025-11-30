# 🌐 Настройка единого домена для Frontend + Backend

Весь сайт на одном домене: **http://cityquest.test**

## 🎯 Архитектура

```
┌──────────────────────────────────────────────┐
│     http://cityquest.test                     │
├──────────────────────────────────────────────┤
│  /              → React (index.html)          │
│  /quest/123     → React (SPA routing)         │
│  /profile       → React (SPA routing)         │
│  /api/*         → Symfony API                 │
└──────────────────────────────────────────────┘
```

### Как работает:

1. **Nginx** слушает на порту 80
2. **`/api/*`** → проксирует на PHP-FPM (Symfony)
3. **Все остальное** → отдает React статику из `/app/frontend/dist`
4. **React Router** обрабатывает клиентский роутинг

## ✅ Преимущества

- ✅ Нет CORS проблем (один origin)
- ✅ Простой деплой (один домен)
- ✅ Быстрее (нет preflight OPTIONS)
- ✅ Production-ready архитектура
- ✅ Не нужен Vite dev server в production
- ✅ Проще настройка SSL/HTTPS

## 🚀 Первоначальная настройка

### 1. Собрать frontend

```bash
# Установить зависимости
make frontend-install

# Собрать production build
make frontend-build
```

Это создаст `/frontend/web/dist` с оптимизированными файлами.

### 2. Настроить CORS в Symfony (все равно нужен!)

```bash
make composer c='require nelmio/cors-bundle'
```

Создать `project/config/packages/nelmio_cors.yaml`:
```yaml
nelmio_cors:
    defaults:
        origin_regex: true
        allow_origin: ['http://cityquest.test']
        allow_methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']
        allow_headers: ['Content-Type', 'Authorization']
        max_age: 3600
```

**Почему нужен CORS если все на одном домене?**
- Для preflight OPTIONS запросов
- Для custom headers (Authorization)
- Для правильной работы с cookies

### 3. Перезапустить контейнеры

```bash
make restart
```

### 4. Проверить

Открыть: **http://cityquest.test**

✅ Должен загрузиться React сайт  
✅ API работает на `/api/*`  
✅ SPA роутинг работает

## 📦 Рабочий процесс

### Development режим

**Вариант 1: Через nginx (как в production)**
```bash
# Собрать frontend
make frontend-build

# Перезапустить
make restart

# Открыть http://cityquest.test
```

**Вариант 2: Dev server (с hot reload)**
```bash
# В одном терминале - backend
make install

# В другом терминале - frontend
make frontend-dev

# Открыть http://localhost:5173 (с proxy на API)
```

### Production деплой

```bash
# Одна команда - собирает frontend и перезапускает
make deploy
```

Или вручную:
```bash
make frontend-build
make restart
```

## 📁 Структура файлов

```
cityQuest/
├── docker/
│   └── nginx/
│       └── conf.d/
│           └── default.conf       # 🔥 Обновлен
│
├── frontend/web/
│   ├── src/                       # Исходники React
│   ├── dist/                      # 🔥 Build (создается автоматически)
│   ├── vite.config.ts             # 🔥 Настройки сборки
│   └── .env.production            # 🔥 Пустой API_URL
│
├── project/                       # Symfony backend
│
├── compose.yaml                   # 🔥 Обновлен (монтирует dist)
├── Makefile                       # 🔥 Новые команды
└── SINGLE_DOMAIN_SETUP.md         # Этот файл
```

🔥 = Обновлено/создано для единого домена

## 🔧 Доступные команды

```bash
# Frontend
make frontend-install    # Установить npm зависимости
make frontend-build      # Собрать production build
make frontend-dev        # Запустить dev server
make frontend-clean      # Очистить build и node_modules

# Деплой
make deploy             # Собрать frontend + перезапустить = полный деплой

# Backend
make install            # Установить и запустить Symfony
make restart            # Перезапустить контейнеры
make test               # Запустить тесты
```

## 🔄 Обновление frontend

После изменений в коде:

```bash
# 1. Собрать
make frontend-build

# 2. Перезапустить nginx (чтобы подхватил новые файлы)
make restart
```

Или одной командой:
```bash
make deploy
```

## 🐛 Troubleshooting

### Проблема: 404 на роутах React

**Симптом:**
- `http://cityquest.test/` работает
- `http://cityquest.test/quest/123` → 404

**Решение:**
Проверить nginx конфиг:
```nginx
location / {
    try_files $uri $uri/ /index.html;  # ← Должна быть эта строка
}
```

Перезапустить:
```bash
make restart
```

### Проблема: API не работает

**Симптом:**
- Frontend загружается
- API запросы возвращают 404 или 502

**Решение:**
1. Проверить что PHP-FPM работает:
```bash
docker compose ps
```

2. Проверить логи:
```bash
docker compose logs nginx
docker compose logs php-fpm
```

3. Проверить что Symfony отвечает:
```bash
docker compose exec php-fpm php bin/console about
```

### Проблема: Статика не обновляется

**Симптом:**
После изменений код не обновился на сайте.

**Решение:**
1. Пересобрать frontend:
```bash
make frontend-build
```

2. Очистить кеш браузера (Ctrl+Shift+R)

3. Проверить что файлы обновились:
```bash
ls -la frontend/web/dist
```

### Проблема: CORS ошибки

**Симптом:**
```
Access-Control-Allow-Origin header is missing
```

**Решение:**
1. Установить nelmio/cors-bundle
2. Создать config/packages/nelmio_cors.yaml
3. Перезапустить: `make restart`

## 📊 Сравнение подходов

### ❌ Без Vite proxy (старый способ)

```
Frontend: http://localhost:5173
Backend:  http://cityquest.test
```

Проблемы:
- Разные origins → CORS
- Нужна настройка CORS для dev
- Dev ≠ Production

### ✅ С единым доменом (текущий способ)

```
Frontend: http://cityquest.test/
Backend:  http://cityquest.test/api
```

Преимущества:
- Один origin → нет CORS проблем
- Dev = Production
- Проще настройка

## 🌍 Production деплой

### На сервере с nginx

```nginx
server {
    listen 80;
    server_name cityquest.com;
    
    root /var/www/cityquest/frontend/web/dist;
    index index.html;
    
    # React SPA
    location / {
        try_files $uri $uri/ /index.html;
    }
    
    # API к Symfony
    location /api {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

### С SSL (HTTPS)

```bash
# Установить certbot
sudo apt install certbot python3-certbot-nginx

# Получить сертификат
sudo certbot --nginx -d cityquest.com

# Автоматическое обновление
sudo certbot renew --dry-run
```

## 🎯 Итого

- ✅ Все на `http://cityquest.test`
- ✅ Нет CORS проблем
- ✅ Простой деплой: `make deploy`
- ✅ Dev можно через `make frontend-dev` с proxy
- ✅ Production-ready архитектура
