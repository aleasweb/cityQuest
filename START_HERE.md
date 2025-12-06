# 🚀 НАЧАТЬ ЗДЕСЬ - Интеграция Frontend с API

## Быстрый старт

### ✅ Что уже работает
- Frontend на http://cityquest.test
- API на http://cityquest.test/api/
- Базовая структура готова

### ⏳ СНАЧАЛА: Quick Wins (45 минут)

Простые задачи для очистки UI:

#### 0.1. Убрать переключение темы (15 мин)
#### 0.2. Убрать поиск (10 мин)
#### 0.3. Изображения из API (5 мин)
#### 0.4. Относительные URL (5 мин)
#### 0.5. Ширина карточек 400px (5 мин)

См. **FRONTEND_API_INTEGRATION_PLAN.md → Фаза 0** для деталей

---

### ⏳ Затем: Критические задачи (2 часа)

#### 1. CORS (15 минут)

```bash
cd /Users/aleas/proj/cityQuest

# Установить bundle
make composer c='require nelmio/cors-bundle'

# Создать конфигурацию
cat > project/config/packages/nelmio_cors.yaml << 'YAML'
nelmio_cors:
    defaults:
        origin_regex: true
        allow_origin: ['http://cityquest.test', 'http://localhost:5173']
        allow_methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']
        allow_headers: ['Content-Type', 'Authorization']
        max_age: 3600
YAML

# Перезапустить
make restart

# Проверить
curl -X OPTIONS http://cityquest.test/api/quests \
  -H "Origin: http://cityquest.test" \
  -v | grep "Access-Control"
```

#### 2. Endpoint для городов (30 минут)

См. FRONTEND_API_INTEGRATION_PLAN.md → Фаза 1 → Задача 1.2

#### 3. Модальное окно входа (1 час)

См. FRONTEND_API_INTEGRATION_PLAN.md → Фаза 2 → Задача 2.1

---

## 📚 Документация

| Файл | Описание |
|------|----------|
| **FRONTEND_API_INTEGRATION_PLAN.md** | Полный план с примерами кода |
| README_DEPLOYMENT.md | Как деплоить изменения |
| VITE_USAGE.md | Как работает сборка |

---

## 🔄 Workflow разработки

### Вариант 1: Production build (медленно, но как в prod)

```bash
# 1. Изменить код
vim frontend/web/src/...

# 2. Пересобрать
./build-frontend-docker.sh

# 3. Проверить
open http://cityquest.test
```

### Вариант 2: Dev server (быстро, с HMR)

```bash
# Один раз:
brew install node
cd frontend/web
npm install

# Каждый раз:
npm run dev
# → http://localhost:5173
```

---

## 📋 Следующие шаги

После CORS, endpoint городов и AuthModal:

1. **QuestDetail** - добавить кнопки "Начать" и "Лайк"
2. **UserProfile** - показать реальный прогресс
3. **Protected Routes** - защитить приватные страницы

См. полный план в **FRONTEND_API_INTEGRATION_PLAN.md**

---

## 🆘 Нужна помощь?

Проверьте:
- **FRONTEND_API_INTEGRATION_PLAN.md** - детальный план
- **README_DEPLOYMENT.md** - команды деплоя
- **CORS_SETUP.md** - настройка CORS

---

## ✅ Чеклист

- [ ] CORS настроен и работает
- [ ] Endpoint `/api/cities` создан
- [ ] AuthModal компонент создан
- [ ] Header обновлен
- [ ] Можно войти/зарегистрироваться
- [ ] QuestDetail интегрирован
- [ ] UserProfile показывает данные
- [ ] Protected Routes работают
- [ ] Error Boundary добавлен
- [ ] Toast уведомления работают

---

**Полный план:** FRONTEND_API_INTEGRATION_PLAN.md  
**Время:** ~10 часов  
**Приоритет:** Высокий
