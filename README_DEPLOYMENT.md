# 🚀 Быстрый деплой CityQuest

## ✅ Сайт запущен!

**URL:** http://cityquest.test

## 📦 Структура

```
http://cityquest.test/
  ├── /              → React Frontend (SPA)
  ├── /quest/123     → React (client-side routing)
  └── /api/*         → Symfony Backend (API)
```

## 🔧 Команды

### При изменении Frontend:
```bash
./build-frontend-docker.sh
```
Этот скрипт:
- Соберет React через Docker (не нужен Node.js!)
- Перезапустит nginx
- Обновит сайт

### При изменении Backend:
```bash
docker compose restart php-fpm
```

### Полный перезапуск:
```bash
docker compose restart
```

### Просмотр логов:
```bash
# Nginx
docker compose logs nginx -f

# PHP-FPM (Symfony)
docker compose logs php-fpm -f

# Все вместе
docker compose logs -f
```

## 🐛 Если сайт не открывается

### 1. Проверить контейнеры
```bash
docker compose ps
```
Все должны быть `Up`

### 2. Пересобрать frontend
```bash
./build-frontend-docker.sh
```

### 3. Пересобрать nginx
```bash
docker compose build nginx
docker compose up -d nginx
```

### 4. Полный перезапуск
```bash
docker compose down
docker compose up -d
```

## 📝 Полезные файлы

- `build-frontend-docker.sh` - Скрипт сборки frontend
- `SINGLE_DOMAIN_SETUP.md` - Полная документация
- `QUICKSTART_SINGLE_DOMAIN.md` - Быстрый старт
- `CORS_SETUP.md` - Настройка CORS

## 🎯 Что дальше

1. **Настроить CORS** (если нужны API запросы из браузера):
   ```bash
   make composer c='require nelmio/cors-bundle'
   ```
   См. `CORS_SETUP.md`

2. **Для разработки с hot reload** (опционально):
   - Установить Node.js: `brew install node`
   - Запустить dev server: `make frontend-dev`
   - Открыть http://localhost:5173

3. **Обновить UI компоненты**:
   - Header (вход/выход)
   - UserProfile
   - Добавить формы входа/регистрации

## 🎉 Готово!

Сайт работает на **http://cityquest.test**

## ✅ Обновление: Картинки квестов

### Проблема
Картинки квестов были недоступны: `http://cityquest.test/s3/q1.png` → 404

### Причина
Nginx конфигурация не имела маршрута для `/s3/` папки со статическими файлами квестов.

### Решение
Добавлен `location /s3/` в nginx конфигурацию:

```nginx
location /s3/ {
    alias /app/public/s3/;
    expires 1y;
    access_log off;
    add_header Cache-Control "public, immutable";
}
```

**Важно:** Этот `location` должен идти **перед** любыми regex паттернами (`~*`), иначе они его перехватят.

### Проверка
```bash
curl -I http://cityquest.test/s3/q1.png
# Должен вернуть: HTTP/1.1 200 OK
```

### Если нужно добавить новые статические папки
Редактируйте `docker/nginx/conf.d/default.conf` и добавьте новый `location`:

```nginx
location /uploads/ {
    alias /app/public/uploads/;
    expires 1y;
}
```

Затем пересоберите nginx:
```bash
docker compose build nginx && docker compose up -d nginx
```
