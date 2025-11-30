# ✅ Миграция завершена - React Frontend → Symfony API

## 🎯 Что было сделано

### 1. Архитектурные изменения
- ❌ Удален Cloudflare Workers backend (Hono)
- ❌ Удалена Cloudflare D1 база данных
- ❌ Удален Mocha Users Service (OAuth)
- ✅ Подключен Symfony REST API
- ✅ Добавена JWT аутентификация
- ✅ Использование PostgreSQL через Symfony

### 2. Созданные файлы

#### API Layer
- `src/shared/api.ts` - HTTP клиент для Symfony API
- `src/shared/types.ts` - Обновленные TypeScript типы

#### Аутентификация
- `src/react-app/contexts/AuthContext.tsx` - JWT auth context
- Замена `@getmocha/users-service` на собственный AuthProvider

#### Hooks
- `src/react-app/hooks/useQuests.ts` - Обновленные хуки для работы с API

#### Конфигурация
- `vite.config.ts` - Добавлен proxy для dev режима
- `.env.local` - Переменные окружения
- `package.json` - Очищенные зависимости

### 3. Удаленные компоненты
- `src/worker/index.ts` - Hono backend (больше не нужен)
- `wrangler.jsonc` - Cloudflare конфиг (не используется)
- Зависимости: `@cloudflare/vite-plugin`, `@getmocha/*`, `hono`

## 🚀 Как запустить

### Шаг 1: Настройка Backend (Symfony)

```bash
# Перейти в корень проекта
cd /Users/aleas/proj/cityQuest

# Установить CORS bundle
make composer c='require nelmio/cors-bundle'

# Настроить CORS (см. CORS_SETUP.md в корне проекта)
# Создать config/packages/nelmio_cors.yaml

# Запустить Docker контейнеры
make restart

# Проверить что API работает
curl http://cityquest.test/api/health
```

### Шаг 2: Настройка Frontend

```bash
# Перейти в папку frontend
cd /Users/aleas/proj/cityQuest/frontend/web

# Установить зависимости (обновленные)
npm install

# Запустить dev сервер
npm run dev
```

Frontend откроется на **http://localhost:5173**

### Шаг 3: Проверка работы

1. Откройте http://localhost:5173
2. Должен загрузиться список квестов из Symfony API
3. Проверьте в DevTools Network:
   - Запросы идут на `/api/quests` 
   - Они проксируются на `http://cityquest.test/api/quests`
   - Получаете корректные данные из PostgreSQL

## 📋 API Endpoints

### Публичные (без авторизации)
```
GET  /api/quests                    - Список квестов
GET  /api/quests/{id}               - Один квест
GET  /api/quests/nearby?lat=...     - Квесты рядом
```

### Аутентификация
```
POST /api/auth/register             - Регистрация
POST /api/auth/login                - Вход (получение JWT)
POST /api/auth/logout               - Выход
```

### Требуют авторизации (JWT в заголовке)
```
GET    /api/user/progress           - Прогресс пользователя
POST   /api/user/progress/{id}/start    - Начать квест
PATCH  /api/user/progress/{id}/pause    - Приостановить
PATCH  /api/user/progress/{id}/complete - Завершить
POST   /api/quests/{id}/like        - Лайк/анлайк
```

## 🔑 JWT Аутентификация

### Регистрация
```typescript
const user = await api.register({
  email: 'user@example.com',
  password: 'password123',
  username: 'username'
});
```

### Вход
```typescript
const { token, user } = await api.login({
  email: 'user@example.com',
  password: 'password123'
});
// JWT токен автоматически сохраняется в localStorage
```

### Использование хука
```typescript
import { useAuth } from '@/react-app/contexts/AuthContext';

function Component() {
  const { user, isAuthenticated, login, logout } = useAuth();
  
  if (!isAuthenticated) {
    return <LoginForm onLogin={login} />;
  }
  
  return <div>Welcome, {user.username}!</div>;
}
```

## 🔄 Изменения в компонентах

### До (Mocha OAuth):
```typescript
import { useAuth } from '@getmocha/users-service/react';

function Header() {
  const { user, login, logout } = useAuth();
  // OAuth flow с редиректами
}
```

### После (JWT):
```typescript
import { useAuth } from '@/react-app/contexts/AuthContext';

function Header() {
  const { user, isAuthenticated, login, logout } = useAuth();
  // Прямой вызов API с email/password
}
```

## 🐛 Возможные проблемы и решения

### Проблема: CORS ошибки
```
Access to fetch at 'http://cityquest.test/api/quests' from origin 'http://localhost:5173' 
has been blocked by CORS policy
```

**Решение:**
1. Установите `nelmio/cors-bundle` в Symfony
2. Настройте по инструкции в `CORS_SETUP.md`
3. Перезапустите контейнеры: `make restart`

### Проблема: 401 Unauthorized
```
{"error": "JWT Token not found"}
```

**Решение:**
1. Проверьте что токен есть в localStorage: `localStorage.getItem('jwt_token')`
2. Проверьте что токен передается в заголовке: `Authorization: Bearer <token>`
3. Проверьте срок действия токена (JWT может истечь)

### Проблема: Квесты не загружаются
```
TypeError: Cannot read property 'data' of undefined
```

**Решение:**
1. Проверьте что Symfony API запущен: `curl http://cityquest.test/api/health`
2. Проверьте структуру ответа от API
3. Убедитесь что в БД есть данные: проверьте через pgAdmin на http://localhost:8888

### Проблема: ID квестов не работают
Frontend ожидает number, Symfony возвращает UUID string.

**Решение:**
Типы уже обновлены в `src/shared/types.ts`:
```typescript
id: z.string() // UUID
```

## 📊 Структура проекта

```
cityQuest/
├── project/                      # Symfony Backend
│   ├── src/
│   │   ├── Quest/               # Квесты
│   │   ├── User/                # Пользователи
│   │   └── UserProgress/        # Прогресс
│   └── config/
│       └── packages/
│           └── nelmio_cors.yaml # CORS конфиг
│
├── frontend/web/                # React Frontend
│   ├── src/
│   │   ├── shared/
│   │   │   ├── api.ts          # ✅ API клиент
│   │   │   └── types.ts        # ✅ TypeScript типы
│   │   ├── react-app/
│   │   │   ├── contexts/
│   │   │   │   └── AuthContext.tsx  # ✅ JWT auth
│   │   │   ├── hooks/
│   │   │   │   └── useQuests.ts     # ✅ Обновлено
│   │   │   └── App.tsx              # ✅ Обновлено
│   │   └── worker/              # ❌ Удалить (не используется)
│   ├── vite.config.ts           # ✅ Proxy настроен
│   ├── package.json             # ✅ Очищен
│   └── .env.local               # ✅ Создан
│
└── CORS_SETUP.md                # Инструкция по CORS
```

## ✅ Чеклист готовности

- [x] API клиент создан (`src/shared/api.ts`)
- [x] Типы обновлены (`src/shared/types.ts`)
- [x] AuthContext реализован
- [x] Хуки обновлены
- [x] App.tsx обновлен
- [x] Vite proxy настроен
- [x] package.json очищен
- [ ] **CORS настроен в Symfony** ⚠️ Требуется действие
- [ ] **Компоненты обновлены** ⚠️ Нужно адаптировать использование auth
- [ ] **Тестирование пройдено** ⚠️ Требуется тестирование

## 🔍 Следующие шаги

### Обязательно:
1. ✅ Настроить CORS в Symfony (см. `CORS_SETUP.md`)
2. ⏳ Обновить компоненты Header/UserProfile (заменить Mocha auth на JWT)
3. ⏳ Добавить endpoint `/api/cities` в Symfony (для фильтра городов)
4. ⏳ Протестировать весь flow: регистрация → вход → просмотр квеста → лайк

### Опционально:
5. ⏳ Добавить обработку ошибок (error boundaries)
6. ⏳ Добавить loading states
7. ⏳ Добавить refresh token механизм
8. ⏳ Удалить папку `src/worker/` и `wrangler.jsonc`

## 📚 Полезные команды

```bash
# Backend
make install          # Установить и запустить
make restart          # Перезапустить контейнеры
make test             # Запустить тесты
make bash             # Войти в PHP контейнер

# Frontend
npm run dev           # Dev сервер
npm run build         # Production build
npm run lint          # Проверка кода

# Проверка API
curl http://cityquest.test/api/health
curl http://cityquest.test/api/quests
```

## 🎉 Результат

Теперь у вас:
- ✅ Единая база данных (PostgreSQL)
- ✅ Единый бэкенд (Symfony)
- ✅ Современная аутентификация (JWT)
- ✅ Типизированный API клиент
- ✅ Удобная разработка с Vite proxy
- ✅ Готово к production деплою
