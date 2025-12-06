# Интеграция Frontend с Symfony Backend

## 🎯 Цель
Подключить React frontend к Symfony API вместо Cloudflare Workers + D1.

## 📝 Текущая архитектура

### Frontend
- **Фреймворк**: Vite + React 19
- **Backend**: Hono (Cloudflare Workers) - `/src/worker/index.ts`
- **База данных**: D1 (Cloudflare SQLite)
- **Аутентификация**: Mocha Users Service (OAuth Google)

### Symfony Backend
- **API**: `/api/quests`, `/api/user/progress`, `/api/auth`
- **База данных**: PostgreSQL 16
- **Аутентификация**: JWT (register/login)

## 🔧 Варианты интеграции

### Вариант 1: Прямые вызовы API (Рекомендуется)
Удалить Hono worker, вызывать Symfony API напрямую через fetch/axios.

**Преимущества:**
- ✅ Простота
- ✅ Единая точка истины (Symfony)
- ✅ Нет дублирования логики

**Недостатки:**
- ⚠️ Нужна настройка CORS
- ⚠️ Изменение аутентификации

### Вариант 2: Vite Proxy (Для разработки)
Проксировать `/api/*` запросы на Symfony через Vite dev server.

**Преимущества:**
- ✅ Удобно для разработки
- ✅ Не нужна настройка CORS

**Недостатки:**
- ⚠️ Только для dev режима
- ⚠️ В production нужен nginx proxy

### Вариант 3: Hono как BFF (Backend for Frontend)
Использовать Hono как прослойку между React и Symfony.

**Преимущества:**
- ✅ Адаптация данных
- ✅ Кеширование
- ✅ Агрегация запросов

**Недостатки:**
- ⚠️ Усложнение архитектуры
- ⚠️ Дублирование логики

## 🚀 Пошаговая интеграция (Вариант 1)

### Шаг 1: Подготовка Backend

1. **Запустить Symfony API**:
```bash
cd /Users/aleas/proj/cityQuest
make install
```

2. **Проверить, что API доступен**:
```bash
curl http://app.test/api/health
```

3. **Настроить CORS в Symfony**:
```bash
composer require nelmio/cors-bundle
```

Конфигурация `config/packages/nelmio_cors.yaml`:
```yaml
nelmio_cors:
    defaults:
        origin_regex: true
        allow_origin: ['http://localhost:5173']
        allow_methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']
        allow_headers: ['Content-Type', 'Authorization']
        expose_headers: ['Link']
        max_age: 3600
```

### Шаг 2: Создание API Client для Frontend

Создать `src/shared/api.ts`:
```typescript
const API_URL = import.meta.env.VITE_API_URL || 'http://app.test';

// Хелпер для запросов
async function apiRequest<T>(
  endpoint: string,
  options: RequestInit = {}
): Promise<T> {
  const token = localStorage.getItem('jwt_token');
  
  const headers: HeadersInit = {
    'Content-Type': 'application/json',
    ...(token && { Authorization: `Bearer ${token}` }),
    ...options.headers,
  };

  const response = await fetch(`${API_URL}${endpoint}`, {
    ...options,
    headers,
  });

  if (!response.ok) {
    throw new Error(`API Error: ${response.statusText}`);
  }

  return response.json();
}

// API методы
export const api = {
  // Quests
  getQuests: (params?: QuestFilters) => {
    const query = new URLSearchParams(params as any).toString();
    return apiRequest<{ data: Quest[] }>(`/api/quests?${query}`);
  },
  
  getQuest: (id: string) => 
    apiRequest<{ data: Quest }>(`/api/quests/${id}`),
  
  toggleLike: (id: string) => 
    apiRequest<{ message: string; data: any }>(`/api/quests/${id}/like`, {
      method: 'POST',
    }),
  
  // User Progress
  getUserProgress: (params?: { status?: string; liked?: boolean }) => {
    const query = new URLSearchParams(params as any).toString();
    return apiRequest<{ data: UserProgress[] }>(`/api/user/progress?${query}`);
  },
  
  startQuest: (questId: string) =>
    apiRequest(`/api/user/progress/${questId}/start`, { method: 'POST' }),
  
  completeQuest: (questId: string) =>
    apiRequest(`/api/user/progress/${questId}/complete`, { method: 'PATCH' }),
  
  pauseQuest: (questId: string) =>
    apiRequest(`/api/user/progress/${questId}/pause`, { method: 'PATCH' }),
  
  // Auth
  register: (data: { email: string; password: string; username: string }) =>
    apiRequest('/api/auth/register', {
      method: 'POST',
      body: JSON.stringify(data),
    }),
  
  login: (email: string, password: string) =>
    apiRequest<{ token: string }>('/api/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email, password }),
    }),
};
```

### Шаг 3: Обновление хуков

Изменить `src/react-app/hooks/useQuests.ts`:
```typescript
import { useState, useEffect } from 'react';
import { Quest, QuestFilters } from '@/shared/types';
import { api } from '@/shared/api';

export function useQuests(filters: QuestFilters) {
  const [quests, setQuests] = useState<Quest[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchQuests = async () => {
      try {
        setLoading(true);
        const result = await api.getQuests(filters);
        setQuests(result.data);
      } catch (err) {
        setError('Failed to load quests');
      } finally {
        setLoading(false);
      }
    };

    fetchQuests();
  }, [filters.city, filters.difficulty, filters.search]);

  return { quests, loading, error };
}

export function useQuest(id: string) {
  const [quest, setQuest] = useState<Quest | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchQuest = async () => {
      try {
        setLoading(true);
        const result = await api.getQuest(id);
        setQuest(result.data);
      } catch (err) {
        setError('Failed to load quest');
      } finally {
        setLoading(false);
      }
    };

    fetchQuest();
  }, [id]);

  return { quest, loading, error };
}
```

### Шаг 4: Обновление аутентификации

Заменить Mocha OAuth на JWT аутентификацию:

1. Удалить зависимость:
```bash
npm uninstall @getmocha/users-service
```

2. Создать контекст аутентификации:
```typescript
// src/react-app/contexts/AuthContext.tsx
import { createContext, useContext, useState, useEffect } from 'react';
import { api } from '@/shared/api';

interface AuthContextType {
  user: User | null;
  login: (email: string, password: string) => Promise<void>;
  register: (data: RegisterData) => Promise<void>;
  logout: () => void;
  isAuthenticated: boolean;
}

const AuthContext = createContext<AuthContextType | null>(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState<User | null>(null);

  const login = async (email: string, password: string) => {
    const result = await api.login(email, password);
    localStorage.setItem('jwt_token', result.token);
    // Получить данные пользователя
    // setUser(userData);
  };

  const logout = () => {
    localStorage.removeItem('jwt_token');
    setUser(null);
  };

  return (
    <AuthContext.Provider value={{ user, login, register, logout, isAuthenticated: !!user }}>
      {children}
    </AuthContext.Provider>
  );
}

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) throw new Error('useAuth must be used within AuthProvider');
  return context;
};
```

### Шаг 5: Запуск

1. **Запустить Backend**:
```bash
make install
```

2. **Запустить Frontend**:
```bash
cd frontend/web
npm install
npm run dev
```

Frontend будет доступен на http://localhost:5173

## 📋 Различия в API

### Фильтрация квестов
**Frontend (D1)**:
```
GET /api/quests?city=Moscow&difficulty=easy&search=text
```

**Backend (Symfony)**:
```
GET /api/quests?city=Moscow&difficulty=easy&sort=likesCount&direction=DESC&limit=20&offset=0
```

### Прогресс пользователя
**Frontend (D1)**:
```
POST /api/quests/:id/complete
GET /api/users/me/quests
```

**Backend (Symfony)**:
```
PATCH /api/user/progress/{questId}/complete
GET /api/user/progress?status=completed
```

## ⚠️ Важные изменения

1. **UUID vs Integer IDs**: Symfony использует UUID, frontend может ожидать числа
2. **Структура ответов**: Нужна адаптация типов
3. **Аутентификация**: JWT вместо OAuth
4. **Endpoint'ы прогресса**: Разная структура URL

## 🔍 Следующие шаги

1. ✅ Настроить CORS в Symfony
2. ✅ Создать API client
3. ✅ Обновить хуки
4. ✅ Заменить аутентификацию
5. ⏳ Добавить endpoint `/api/cities` в Symfony
6. ⏳ Синхронизировать структуру данных
7. ⏳ Тестирование интеграции

## 🐛 Возможные проблемы

### CORS ошибки
**Решение**: Установить и настроить `nelmio/cors-bundle`

### Разные форматы данных
**Решение**: Создать адаптеры в `api.ts`

### JWT не передается
**Решение**: Проверить заголовки и localStorage
