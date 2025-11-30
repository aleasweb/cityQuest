# CityQuest Frontend

React приложение для платформы CityQuest, интегрированное с Symfony API.

## 🚀 Быстрый старт

```bash
# 1. Настроить CORS в Symfony (ОБЯЗАТЕЛЬНО!)
cd /Users/aleas/proj/cityQuest
make composer c='require nelmio/cors-bundle'
# Создать config/packages/nelmio_cors.yaml (см. TODO.md)
make restart

# 2. Установить зависимости и запустить
cd frontend/web
npm install
npm run dev
```

Откройте **http://localhost:5173**

## 📚 Документация

### Основная документация
- **[QUICK_START.md](./QUICK_START.md)** - Быстрый старт за 3 шага
- **[TODO.md](./TODO.md)** - Список задач для финализации
- **[MIGRATION_COMPLETE.md](./MIGRATION_COMPLETE.md)** - Полная документация миграции

### Дополнительная документация
- **[CHANGES_SUMMARY.md](./CHANGES_SUMMARY.md)** - Сводка всех изменений
- **[INTEGRATION.md](./INTEGRATION.md)** - Варианты интеграции с backend
- **[../CORS_SETUP.md](../../../CORS_SETUP.md)** - Настройка CORS в Symfony

## 🏗️ Архитектура

```
frontend/web/
├── src/
│   ├── shared/
│   │   ├── api.ts          # 🔥 HTTP клиент для Symfony API
│   │   └── types.ts        # 🔥 TypeScript типы
│   ├── react-app/
│   │   ├── contexts/
│   │   │   ├── AuthContext.tsx    # 🔥 JWT аутентификация
│   │   │   └── ThemeContext.tsx
│   │   ├── hooks/
│   │   │   └── useQuests.ts       # 🔥 Хуки для работы с API
│   │   ├── components/
│   │   │   ├── Header.tsx
│   │   │   ├── QuestCard.tsx
│   │   │   └── ...
│   │   ├── pages/
│   │   │   ├── Home.tsx
│   │   │   ├── QuestDetail.tsx
│   │   │   └── UserProfile.tsx
│   │   └── App.tsx
│   └── worker/              # ❌ Не используется (можно удалить)
├── .env.local               # 🔥 Переменные окружения
├── vite.config.ts           # 🔥 Vite с proxy
└── package.json             # 🔥 Обновленные зависимости
```

🔥 = Обновлено для интеграции с Symfony

## 🔌 API Integration

### Подключение к Symfony API

```typescript
import { api } from '@/shared/api';

// Получить квесты
const quests = await api.getQuests({ city: 'Moscow' });

// Получить один квест
const quest = await api.getQuest('quest-uuid');

// Лайк (требует авторизации)
await api.toggleLike('quest-uuid');
```

### Аутентификация

```typescript
import { useAuth } from '@/react-app/contexts/AuthContext';

function MyComponent() {
  const { user, isAuthenticated, login, logout } = useAuth();
  
  const handleLogin = async () => {
    await login({ email: 'user@example.com', password: 'pass' });
  };
  
  return (
    <div>
      {isAuthenticated ? (
        <p>Привет, {user.username}!</p>
      ) : (
        <button onClick={handleLogin}>Войти</button>
      )}
    </div>
  );
}
```

## 🛠️ Технологии

- **React 19** - UI фреймворк
- **Vite 6** - Сборщик и dev server
- **TypeScript** - Типизация
- **Tailwind CSS** - Стили
- **React Router 7** - Роутинг
- **Zod** - Валидация схем
- **Lucide React** - Иконки

## 📋 Доступные команды

```bash
npm run dev       # Запуск dev сервера (http://localhost:5173)
npm run build     # Сборка для production
npm run preview   # Просмотр production сборки
npm run lint      # Проверка кода
```

## 🔗 API Endpoints

### Публичные (без авторизации)
- `GET /api/quests` - Список квестов
- `GET /api/quests/{id}` - Один квест
- `GET /api/quests/nearby` - Квесты рядом
- `POST /api/auth/register` - Регистрация
- `POST /api/auth/login` - Вход

### Требуют авторизации (JWT)
- `GET /api/user/progress` - Прогресс пользователя
- `POST /api/user/progress/{id}/start` - Начать квест
- `PATCH /api/user/progress/{id}/complete` - Завершить квест
- `POST /api/quests/{id}/like` - Лайк/анлайк

## ⚠️ Требования

- Node.js 18+
- npm 9+
- Symfony API запущен на `http://cityquest.test`
- CORS настроен в Symfony

## 🐛 Troubleshooting

### CORS ошибки
Убедитесь что `nelmio/cors-bundle` установлен и настроен в Symfony.
См. [CORS_SETUP.md](../../../CORS_SETUP.md)

### API не отвечает
```bash
# Проверить что Symfony запущен
curl http://cityquest.test/api/health

# Перезапустить
cd /Users/aleas/proj/cityQuest
make restart
```

### 401 Unauthorized
JWT токен истек или отсутствует. Войдите заново.

## 📞 Помощь

Если возникли проблемы:
1. Прочитайте [TODO.md](./TODO.md) - там есть решения распространенных проблем
2. Проверьте консоль браузера (F12)
3. Проверьте Network tab на CORS ошибки
4. Проверьте логи Symfony: `make bash` → `tail -f var/log/dev.log`

## 🎯 Статус интеграции

- ✅ API клиент создан
- ✅ Типы обновлены
- ✅ JWT аутентификация реализована
- ✅ Хуки обновлены
- ✅ Vite proxy настроен
- ⏳ CORS требуется настроить в Symfony
- ⏳ Компоненты требуют обновления (Header, UserProfile)
- ⏳ Тестирование требуется

## 📖 История изменений

Проект мигрирован с **Cloudflare Workers (Hono) + D1** на **Symfony API + PostgreSQL**.

Подробности см. в [MIGRATION_COMPLETE.md](./MIGRATION_COMPLETE.md)

---

**Версия:** 1.0  
**Дата миграции:** 2025-11-30  
**Backend API:** http://cityquest.test/api
