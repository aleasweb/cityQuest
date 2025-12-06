# 🚀 Быстрый старт - Frontend интеграция с Symfony

## 1️⃣ Настроить CORS (ОБЯЗАТЕЛЬНО!)

```bash
cd /Users/aleas/proj/cityQuest
make composer c='require nelmio/cors-bundle'
```

Создать `project/config/packages/nelmio_cors.yaml`:

```yaml
nelmio_cors:
    defaults:
        origin_regex: true
        allow_origin: ['http://localhost:5173']
        allow_methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']
        allow_headers: ['Content-Type', 'Authorization']
        max_age: 3600
```

Перезапустить:
```bash
make restart
```

## 2️⃣ Запустить Frontend

```bash
cd /Users/aleas/proj/cityQuest/frontend/web
npm install
npm run dev
```

Открыть: **http://localhost:5173**

## 3️⃣ Проверить

- ✅ Список квестов загружается
- ✅ Можно кликнуть на квест
- ✅ Нет CORS ошибок в консоли

## ⚠️ Если не работает

### CORS ошибка
```bash
# Проверить что CORS bundle установлен
cd /Users/aleas/proj/cityQuest
make composer c='show nelmio/cors-bundle'

# Проверить конфиг
cat project/config/packages/nelmio_cors.yaml

# Перезапустить
make restart
```

### API не отвечает
```bash
# Проверить что Symfony запущен
curl http://cityquest.test/api/health

# Если не работает - запустить заново
make install
```

### Frontend ошибки
```bash
cd /Users/aleas/proj/cityQuest/frontend/web

# Очистить и переустановить
rm -rf node_modules package-lock.json
npm install
npm run dev
```

## 📚 Документация

- **MIGRATION_COMPLETE.md** - Полное описание изменений
- **CORS_SETUP.md** (в корне) - Детальная настройка CORS
- **INTEGRATION.md** - Варианты интеграции
