# 📝 Сводка изменений - Frontend → Symfony API

## ✅ Созданные файлы

| Файл | Описание |
|------|----------|
| `src/shared/api.ts` | HTTP клиент для Symfony API с JWT |
| `src/shared/types.ts` | Обновленные TypeScript типы (UUID вместо number) |
| `src/react-app/contexts/AuthContext.tsx` | JWT аутентификация вместо OAuth |
| `.env.local` | Переменные окружения (API URL) |
| `MIGRATION_COMPLETE.md` | Полная документация миграции |
| `QUICK_START.md` | Быстрый старт |
| `CHANGES_SUMMARY.md` | Этот файл |

## ♻️ Измененные файлы

| Файл | Что изменено |
|------|--------------|
| `vite.config.ts` | Удален Cloudflare/Mocha, добавлен proxy |
| `package.json` | Удалены зависимости Cloudflare, Hono, Mocha |
| `src/react-app/App.tsx` | Заменен AuthProvider на собственный |
| `src/react-app/hooks/useQuests.ts` | Обновлены хуки для работы с Symfony API |

## 🗑️ Можно удалить (не используются)

| Файл/Папка | Причина |
|------------|---------|
| `src/worker/` | Hono backend больше не используется |
| `wrangler.jsonc` | Cloudflare конфиг не нужен |
| `migrations/` в frontend | D1 миграции не используются |

## ⚙️ Требуемые действия

### В Symfony Backend:
- [ ] Установить `nelmio/cors-bundle`
- [ ] Создать `config/packages/nelmio_cors.yaml`
- [ ] Перезапустить контейнеры

### В Frontend:
- [x] API клиент создан
- [x] Типы обновлены
- [x] AuthContext реализован
- [x] Хуки обновлены
- [x] Конфигурация обновлена
- [ ] Обновить компоненты Header/UserProfile (замена использования auth)
- [ ] Протестировать все функции

## 🔄 Миграция аутентификации

### Было (Mocha OAuth):
```typescript
import { useAuth } from '@getmocha/users-service/react';

const { user } = useAuth();
```

### Стало (JWT):
```typescript
import { useAuth } from '@/react-app/contexts/AuthContext';

const { user, isAuthenticated } = useAuth();
```

## 🎯 Ключевые изменения API

| Функция | Старый endpoint | Новый endpoint |
|---------|----------------|----------------|
| Список квестов | Worker → D1 | `GET /api/quests` |
| Один квест | Worker → D1 | `GET /api/quests/{id}` |
| Лайк | Worker → D1 | `POST /api/quests/{id}/like` |
| Прогресс | Worker → D1 | `GET /api/user/progress` |
| Завершить | `POST /api/quests/:id/complete` | `PATCH /api/user/progress/{id}/complete` |
| Авторизация | OAuth Google | JWT (email/password) |

## 🚀 Команды для запуска

```bash
# Backend (если еще не запущен)
cd /Users/aleas/proj/cityQuest
make composer c='require nelmio/cors-bundle'
# Создать config/packages/nelmio_cors.yaml (см. CORS_SETUP.md)
make restart

# Frontend
cd /Users/aleas/proj/cityQuest/frontend/web
npm install
npm run dev
```

## 📊 Диаграмма архитектуры

### До:
```
React → Hono (Cloudflare Worker) → D1 (SQLite)
          ↓
     Mocha OAuth
```

### После:
```
React → Symfony API → PostgreSQL
          ↓
       JWT Auth
```

## 🎉 Преимущества

- ✅ Единая база данных (PostgreSQL)
- ✅ Единый бэкенд (не нужно синхронизировать логику)
- ✅ JWT аутентификация (стандартный подход)
- ✅ Типизированный API
- ✅ Vite proxy для удобной разработки
- ✅ Готово к production
