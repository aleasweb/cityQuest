# 🚀 Быстрый старт - Единый домен

## 3 команды для запуска

```bash
# 1. Установить frontend зависимости
make frontend-install

# 2. Собрать frontend и задеплоить
make deploy

# 3. Открыть браузер
open http://cityquest.test
```

✅ Готово! Сайт работает на **http://cityquest.test**

## Что произошло?

```
make deploy выполнил:
  1. npm run build          # Собрал React → frontend/web/dist
  2. make restart           # Перезапустил Docker контейнеры
```

## Nginx теперь отдает:

```
http://cityquest.test/          → React (frontend/web/dist/index.html)
http://cityquest.test/quest/1   → React (SPA routing)
http://cityquest.test/api/*     → Symfony API (PHP-FPM)
```

## Если нужен CORS

```bash
# 1. Установить bundle
make composer c='require nelmio/cors-bundle'

# 2. Создать config/packages/nelmio_cors.yaml
cat > project/config/packages/nelmio_cors.yaml << 'YAML'
nelmio_cors:
    defaults:
        origin_regex: true
        allow_origin: ['http://cityquest.test']
        allow_methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']
        allow_headers: ['Content-Type', 'Authorization']
        max_age: 3600
YAML

# 3. Перезапустить
make restart
```

## Development с hot reload

Если нужна автоматическая перезагрузка при изменениях:

```bash
# В одном терминале
make install    # Backend

# В другом терминале
make frontend-dev    # Frontend на localhost:5173 (с proxy)
```

## После изменений в коде

```bash
make deploy
```

Эта команда:
- Собирает новый build
- Перезапускает контейнеры
- Обновляет сайт

## Проверка

```bash
# Frontend
curl http://cityquest.test/

# API
curl http://cityquest.test/api/health
curl http://cityquest.test/api/quests
```

## 📚 Подробности

См. **SINGLE_DOMAIN_SETUP.md**
