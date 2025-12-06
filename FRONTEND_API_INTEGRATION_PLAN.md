# 📋 План доработки Frontend для работы с API

## 🎯 Цель
Полная интеграция React frontend с Symfony API для работы всех функций приложения.

## 📊 Текущее состояние

### ✅ Готово
- [x] Nginx настроен для единого домена (cityquest.test)
- [x] API клиент создан (`src/shared/api.ts`)
- [x] TypeScript типы определены (`src/shared/types.ts`)
- [x] AuthContext реализован (`src/react-app/contexts/AuthContext.tsx`)
- [x] Хуки обновлены (`src/react-app/hooks/useQuests.ts`)
- [x] App.tsx использует новый AuthProvider

### ⏳ Требуется доработка
- [ ] Формы входа/регистрации
- [ ] Обновление компонентов UI
- [ ] CORS настройка в Symfony
- [ ] Обработка ошибок
- [ ] Loading states
- [ ] Защищенные роуты
- [ ] Интеграция с реальными API

## 📝 Детальный план

---

## Фаза 0: Подготовительные задачи (Quick Wins - 1 час)

### 0.1. Убрать переключение темной/светлой темы ⚡

**Проблема:** Нужна только светлая тема.

**Задачи:**

1. **Обновить `ThemeContext.tsx`**

**Файл:** `src/react-app/contexts/ThemeContext.tsx`
```typescript
import { createContext, useContext, ReactNode } from 'react';

interface ThemeContextType {
  theme: 'light';
}

const ThemeContext = createContext<ThemeContextType>({ theme: 'light' });

export function ThemeProvider({ children }: { children: ReactNode }) {
  return (
    <ThemeContext.Provider value={{ theme: 'light' }}>
      {children}
    </ThemeContext.Provider>
  );
}

export function useTheme() {
  return useContext(ThemeContext);
}
```

2. **Обновить `Header.tsx`**

Удалить кнопку переключения темы:
```typescript
// Удалить весь блок:
{/* Theme Toggle */}
<button onClick={toggleTheme} ... >
  ...
</button>
```

3. **Удалить `dark:` классы из всех компонентов** (опционально)

Можно оставить для будущего, но удалить использование `useTheme()` где не нужно.

**Время:** 15 минут  
**Сложность:** Очень низкая

---

### 0.2. Убрать поиск по названию ⚡

**Проблема:** Поиск по названию не нужен.

**Задачи:**

1. **Обновить `Filters.tsx`**

**Файл:** `src/react-app/components/Filters.tsx`

Удалить поле поиска:
```typescript
// Удалить блок с поиском:
<div className="mb-4">
  <input
    type="text"
    placeholder="Поиск по названию..."
    value={filters.search || ''}
    onChange={(e) => onFiltersChange({ ...filters, search: e.target.value })}
    className="..."
  />
</div>
```

2. **Обновить `types.ts`**

**Файл:** `src/shared/types.ts`
```typescript
export const QuestFiltersSchema = z.object({
  city: z.string().optional(),
  difficulty: z.enum(['легкие', 'средние', 'сложные']).optional(),
  // Удалить: search: z.string().optional()
});
```

3. **Обновить `api.ts`**

**Файл:** `src/shared/api.ts`
```typescript
getQuests: async (filters?: QuestFilters): Promise<Quest[]> => {
    const params = new URLSearchParams();
    
    if (filters?.city) params.append('city', filters.city);
    if (filters?.difficulty) params.append('difficulty', filters.difficulty);
    // Удалить: if (filters?.search) params.append('search', filters.search);
    
    // ...
}
```

**Время:** 10 минут  
**Сложность:** Очень низкая

---

### 0.3. Выводить изображение квеста из API ⚡

**Проблема:** Изображения не отображаются или используют хардкод.

**Задачи:**

1. **Обновить `QuestCard.tsx`**

**Файл:** `src/react-app/components/QuestCard.tsx`

```typescript
// Изменить src изображения:
<img 
  src={quest.imageUrl || '/placeholder.png'} 
  alt={quest.title}
  className="w-full h-48 object-cover"
  onError={(e) => {
    // Fallback если изображение не загрузилось
    e.currentTarget.src = '/placeholder.png';
  }}
/>
```

2. **Проверить что API возвращает imageUrl**

API уже возвращает поле `imageUrl` в формате `/s3/q1.png`, это правильно.

**Время:** 5 минут  
**Сложность:** Очень низкая

---

### 0.4. Убрать абсолютные адреса для API и изображений ⚡ ВАЖНО

**Проблема:** Используются абсолютные URL вместо относительных.

**Задачи:**

1. **Обновить `api.ts`**

**Файл:** `src/shared/api.ts`

```typescript
// Изменить с:
const API_URL = import.meta.env.VITE_API_URL || 'http://cityquest.test/api';

// На:
const API_URL = import.meta.env.VITE_API_URL || '/api';
```

Теперь все запросы будут относительными:
- `fetch('/api/quests')` вместо `fetch('http://cityquest.test/api/quests')`

2. **Проверить `.env.local`**

**Файл:** `frontend/web/.env.local`
```bash
# Должно быть пусто или с относительным путем
VITE_API_URL=
```

3. **Проверить `.env.production`**

**Файл:** `frontend/web/.env.production`
```bash
# Должно быть пусто для относительных путей
VITE_API_URL=
```

4. **Изображения уже используют относительные пути**

API возвращает `/s3/q1.png` - это уже относительный путь, все ОК! ✅

**Время:** 5 минут  
**Сложность:** Очень низкая

---

### 0.5. Установить ширину карточки квеста 400px ⚡

**Проблема:** Карточки квестов должны быть фиксированной ширины.

**Задачи:**

1. **Обновить `QuestCard.tsx`**

**Файл:** `src/react-app/components/QuestCard.tsx`

```typescript
export default function QuestCard({ quest, onClick }: Props) {
  return (
    <div 
      onClick={onClick}
      className="w-[400px] flex-shrink-0 bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow cursor-pointer overflow-hidden"
    >
      {/* Содержимое карточки */}
    </div>
  );
}
```

2. **Обновить `QuestSlider.tsx`**

**Файл:** `src/react-app/components/QuestSlider.tsx`

```typescript
export default function QuestSlider({ quests, onQuestClick }: Props) {
  return (
    <div className="overflow-x-auto">
      <div className="flex gap-4 pb-4">
        {quests.map((quest) => (
          <QuestCard 
            key={quest.id} 
            quest={quest} 
            onClick={() => onQuestClick(quest)} 
          />
        ))}
      </div>
    </div>
  );
}
```

Класс `w-[400px]` установит фиксированную ширину 400px.

**Время:** 5 минут  
**Сложность:** Очень низкая

---

### ✅ Результат Фазы 0

После выполнения всех задач:
- ✅ Только светлая тема
- ✅ Нет поиска по названию
- ✅ Изображения из API отображаются
- ✅ Все запросы относительные (без абсолютных URL)
- ✅ Карточки квестов 400px ширины

**Общее время Фазы 0:** ~45 минут  
**Можно делать параллельно или последовательно**

---

## Фаза 1: Базовая инфраструктура (Критично)

### 1.1. Настройка CORS в Symfony ⚡ ВЫСОКИЙ ПРИОРИТЕТ

**Проблема:** API возвращает CORS ошибки при запросах из браузера.

**Задачи:**
```bash
# 1. Установить bundle
make composer c='require nelmio/cors-bundle'

# 2. Создать конфигурацию
```

**Файл:** `project/config/packages/nelmio_cors.yaml`
```yaml
nelmio_cors:
    defaults:
        origin_regex: true
        allow_origin: ['http://cityquest.test', 'http://localhost:5173']
        allow_methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']
        allow_headers: ['Content-Type', 'Authorization']
        expose_headers: ['Link']
        max_age: 3600
    paths:
        '^/api/':
            allow_origin: ['*']
```

**Проверка:**
```bash
curl -X OPTIONS http://cityquest.test/api/quests \
  -H "Origin: http://cityquest.test" \
  -v | grep "Access-Control"
```

**Время:** 15 минут  
**Сложность:** Низкая

---

### 1.2. Endpoint для получения городов

**Проблема:** `useCities()` хук использует workaround, нужен отдельный endpoint.

**Задачи:**

1. **Backend:** Добавить метод в `QuestController.php`
   ```php
   #[Route('/api/cities', name: 'api_cities', methods: ['GET'])]
   public function getCities(): JsonResponse
   {
       $cities = $this->questListService->getDistinctCities();
       return $this->json(['data' => $cities]);
   }
   ```

2. **Backend:** Добавить метод в `QuestListService.php`
   ```php
   public function getDistinctCities(): array
   {
       return $this->questRepository->findDistinctCities();
   }
   ```

3. **Backend:** Добавить метод в `DoctrineQuestRepository.php`
   ```php
   public function findDistinctCities(): array
   {
       $result = $this->createQueryBuilder('q')
           ->select('DISTINCT q.city')
           ->where('q.city IS NOT NULL')
           ->orderBy('q.city', 'ASC')
           ->getQuery()
           ->getResult();
       
       return array_map(fn($row) => $row['city'], $result);
   }
   ```

4. **Frontend:** Обновить `api.ts`
   ```typescript
   getCities: async (): Promise<string[]> => {
       const response = await apiRequest<{ data: string[] }>('/cities');
       return response.data || [];
   }
   ```

5. **Frontend:** Обновить `useQuests.ts`
   ```typescript
   export function useCities() {
       const [cities, setCities] = useState<string[]>([]);
       const [loading, setLoading] = useState(true);

       useEffect(() => {
           const fetchCities = async () => {
               try {
                   const data = await api.getCities();
                   setCities(data);
               } catch (err) {
                   console.error('Failed to load cities:', err);
               } finally {
                   setLoading(false);
               }
           };
           fetchCities();
       }, []);

       return { cities, loading };
   }
   ```

**Время:** 30 минут  
**Сложность:** Низкая

---

## Фаза 2: Аутентификация и формы (Критично)

### 2.1. Модальное окно входа/регистрации

**Проблема:** Нет UI для входа/регистрации.

**Задачи:**

1. **Создать компонент `AuthModal.tsx`**

**Файл:** `src/react-app/components/AuthModal.tsx`
```typescript
import { useState } from 'react';
import { X } from 'lucide-react';
import { useAuth } from '@/react-app/contexts/AuthContext';

interface Props {
  isOpen: boolean;
  onClose: () => void;
}

type Tab = 'login' | 'register';

export default function AuthModal({ isOpen, onClose }: Props) {
  const [tab, setTab] = useState<Tab>('login');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [username, setUsername] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  
  const { login, register } = useAuth();

  if (!isOpen) return null;

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      if (tab === 'login') {
        await login({ email, password });
      } else {
        await register({ email, password, username });
      }
      onClose();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Произошла ошибка');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div className="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md">
        {/* Header */}
        <div className="flex justify-between items-center mb-4">
          <h2 className="text-2xl font-bold text-gray-900 dark:text-white">
            {tab === 'login' ? 'Вход' : 'Регистрация'}
          </h2>
          <button onClick={onClose} className="text-gray-500 hover:text-gray-700">
            <X className="w-6 h-6" />
          </button>
        </div>

        {/* Tabs */}
        <div className="flex space-x-4 mb-4">
          <button
            onClick={() => setTab('login')}
            className={`flex-1 py-2 ${tab === 'login' ? 'border-b-2 border-orange-500' : ''}`}
          >
            Вход
          </button>
          <button
            onClick={() => setTab('register')}
            className={`flex-1 py-2 ${tab === 'register' ? 'border-b-2 border-orange-500' : ''}`}
          >
            Регистрация
          </button>
        </div>

        {/* Error */}
        {error && (
          <div className="mb-4 p-3 bg-red-100 text-red-700 rounded">
            {error}
          </div>
        )}

        {/* Form */}
        <form onSubmit={handleSubmit}>
          {tab === 'register' && (
            <div className="mb-4">
              <label className="block text-sm font-medium mb-2">Имя пользователя</label>
              <input
                type="text"
                value={username}
                onChange={(e) => setUsername(e.target.value)}
                required
                className="w-full px-3 py-2 border rounded-lg"
              />
            </div>
          )}

          <div className="mb-4">
            <label className="block text-sm font-medium mb-2">Email</label>
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
              className="w-full px-3 py-2 border rounded-lg"
            />
          </div>

          <div className="mb-4">
            <label className="block text-sm font-medium mb-2">Пароль</label>
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
              minLength={6}
              className="w-full px-3 py-2 border rounded-lg"
            />
          </div>

          <button
            type="submit"
            disabled={loading}
            className="w-full bg-orange-500 hover:bg-orange-600 text-white py-2 rounded-lg disabled:opacity-50"
          >
            {loading ? 'Загрузка...' : (tab === 'login' ? 'Войти' : 'Зарегистрироваться')}
          </button>
        </form>
      </div>
    </div>
  );
}
```

2. **Обновить `Header.tsx`**

Добавить состояние для модального окна:
```typescript
const [isAuthModalOpen, setIsAuthModalOpen] = useState(false);

// Заменить alert на открытие модального окна
const handleLogin = () => {
  setIsAuthModalOpen(true);
};

// Добавить в return:
<AuthModal 
  isOpen={isAuthModalOpen} 
  onClose={() => setIsAuthModalOpen(false)} 
/>
```

**Время:** 1 час  
**Сложность:** Средняя

---

### 2.2. Обновление Header для работы с JWT

**Проблема:** Header показывает TODO вместо реальной функциональности.

**Задачи:** (Уже частично сделано в п. 2.1)

**Файл:** `src/react-app/components/Header.tsx`
- [x] Импорт useAuth из нового контекста
- [x] Использование isAuthenticated вместо user
- [ ] Добавление AuthModal
- [ ] Обработка logout

**Время:** 30 минут (вместе с 2.1)  
**Сложность:** Низкая

---

## Фаза 3: Интеграция с API в компонентах (Важно)

### 3.1. Страница деталей квеста (QuestDetail.tsx)

**Проблема:** Страница не полностью использует API.

**Задачи:**

1. Обновить получение квеста через API
2. Добавить кнопку "Начать квест" (API: `POST /api/user/progress/{questId}/start`)
3. Добавить кнопку "Лайк" (API: `POST /api/quests/{id}/like`)
4. Показать статус прохождения (если авторизован)

**Пример:**
```typescript
const handleStartQuest = async () => {
  if (!isAuthenticated) {
    setShowAuthModal(true);
    return;
  }
  
  try {
    await api.startQuest(questId);
    // Показать успех
  } catch (error) {
    // Показать ошибку
  }
};
```

**Время:** 1 час  
**Сложность:** Средняя

---

### 3.2. Профиль пользователя (UserProfile.tsx)

**Проблема:** Показывает заглушки вместо реальных данных.

**Задачи:**

1. Получить прогресс через API: `GET /api/user/progress`
2. Показать список пройденных квестов
3. Показать список лайкнутых квестов
4. Показать статистику

**Пример:**
```typescript
const [progress, setProgress] = useState<UserProgress[]>([]);

useEffect(() => {
  const fetchProgress = async () => {
    try {
      const data = await api.getUserProgress();
      setProgress(data);
    } catch (error) {
      // Handle error
    }
  };
  fetchProgress();
}, []);
```

**Время:** 1.5 часа  
**Сложность:** Средняя

---

### 3.3. Фильтры (Filters.tsx)

**Проблема:** Фильтр по городам использует workaround.

**Задачи:**

1. Использовать новый endpoint `/api/cities`
2. Добавить loading state
3. Обработать ошибки

**Время:** 30 минут  
**Сложность:** Низкая

---

## Фаза 4: UX улучшения (Важно)

### 4.1. Глобальная обработка ошибок

**Создать:** `src/react-app/components/ErrorBoundary.tsx`

```typescript
import { Component, ReactNode } from 'react';

interface Props {
  children: ReactNode;
}

interface State {
  hasError: boolean;
  error: Error | null;
}

export class ErrorBoundary extends Component<Props, State> {
  constructor(props: Props) {
    super(props);
    this.state = { hasError: false, error: null };
  }

  static getDerivedStateFromError(error: Error): State {
    return { hasError: true, error };
  }

  render() {
    if (this.state.hasError) {
      return (
        <div className="min-h-screen flex items-center justify-center bg-gray-50">
          <div className="text-center">
            <h1 className="text-2xl font-bold text-gray-900 mb-4">
              Что-то пошло не так
            </h1>
            <p className="text-gray-600 mb-4">{this.state.error?.message}</p>
            <button
              onClick={() => window.location.reload()}
              className="px-6 py-2 bg-orange-500 text-white rounded-lg"
            >
              Перезагрузить страницу
            </button>
          </div>
        </div>
      );
    }

    return this.props.children;
  }
}
```

Обернуть App в ErrorBoundary в `main.tsx`.

**Время:** 30 минут  
**Сложность:** Низкая

---

### 4.2. Компонент уведомлений (Toast)

**Создать:** `src/react-app/components/Toast.tsx`

Для показа успешных действий и ошибок.

**Библиотека:** react-hot-toast или sonner

```bash
npm install react-hot-toast
```

**Время:** 45 минут  
**Сложность:** Низкая

---

### 4.3. Loading скелетоны

**Создать:** `src/react-app/components/QuestCardSkeleton.tsx`

Показывать во время загрузки квестов вместо спиннера.

**Время:** 30 минут  
**Сложность:** Низкая

---

## Фаза 5: Защита роутов (Важно)

### 5.1. Protected Route компонент

**Создать:** `src/react-app/components/ProtectedRoute.tsx`

```typescript
import { Navigate } from 'react-router';
import { useAuth } from '@/react-app/contexts/AuthContext';

interface Props {
  children: React.ReactNode;
}

export default function ProtectedRoute({ children }: Props) {
  const { isAuthenticated, isLoading } = useAuth();

  if (isLoading) {
    return <div>Загрузка...</div>;
  }

  if (!isAuthenticated) {
    return <Navigate to="/" replace />;
  }

  return <>{children}</>;
}
```

Обернуть защищенные роуты в App.tsx:
```typescript
<Route 
  path="/profile" 
  element={
    <ProtectedRoute>
      <UserProfile />
    </ProtectedRoute>
  } 
/>
```

**Время:** 30 минут  
**Сложность:** Низкая

---

## Фаза 6: Оптимизация (Опционально)

### 6.1. React Query для кеширования

```bash
npm install @tanstack/react-query
```

Заменить `useState` + `useEffect` на `useQuery`.

**Время:** 2 часа  
**Сложность:** Средняя

---

### 6.2. Оптимизация изображений

- Добавить lazy loading для картинок
- Добавить placeholder'ы

**Время:** 1 час  
**Сложность:** Низкая

---

## 📊 Приоритизация задач

### ⚡ Quick Wins (быстрые победы - начать с этого!)
0. **Фаза 0: Подготовительные задачи** (45 мин)
   - Убрать переключение темы
   - Убрать поиск
   - Настроить изображения
   - Относительные URL
   - Ширина карточек 400px

**Итого:** ~45 минут

### 🔴 Критические (должны быть сделаны первыми)
1. **CORS настройка** (15 мин)
2. **Модальное окно входа** (1 час)
3. **Endpoint для городов** (30 мин)

**Итого:** ~2 часа

### 🟡 Важные (для полной функциональности)
4. **QuestDetail интеграция** (1 час)
5. **UserProfile интеграция** (1.5 часа)
6. **Filters обновление** (30 мин)
7. **Protected Routes** (30 мин)
8. **Error Boundary** (30 мин)

**Итого:** ~4 часа

### 🟢 Опциональные (улучшения UX)
9. **Toast уведомления** (45 мин)
10. **Loading скелетоны** (30 мин)
11. **React Query** (2 часа)
12. **Оптимизация изображений** (1 час)

**Итого:** ~4 часа

---

## 📈 Порядок выполнения (рекомендуется)

### Сначала: Quick Wins (45 минут):
0. ✅ Фаза 0: Подготовительные задачи
   - Убрать тему
   - Убрать поиск  
   - Изображения из API
   - Относительные URL
   - Ширина карточек

**Результат:** UI очищен и готов к интеграции

### День 1 (2 часа):
1. ✅ CORS настройка
2. ✅ Endpoint для городов
3. ✅ Модальное окно входа

**Результат:** Базовая аутентификация работает

### День 2 (4 часа):
4. ✅ QuestDetail интеграция
5. ✅ UserProfile интеграция
6. ✅ Filters обновление
7. ✅ Protected Routes
8. ✅ Error Boundary

**Результат:** Все основные функции работают

### День 3 (4 часа):
9. ✅ Toast уведомления
10. ✅ Loading скелетоны
11. ✅ Тестирование и баг-фиксы
12. ✅ Документация

**Результат:** Production-ready приложение

---

## 🔧 Инструменты для разработки

### Рекомендуемые библиотеки:

```bash
# Уведомления
npm install react-hot-toast

# Иконки (уже есть)
# lucide-react

# Формы (опционально)
npm install react-hook-form zod

# Кеширование (опционально)
npm install @tanstack/react-query
```

---

## ✅ Чеклист готовности

### Backend
- [ ] CORS настроен
- [ ] Endpoint `/api/cities` добавлен
- [ ] JWT корректно работает
- [ ] Все endpoint'ы протестированы

### Frontend
- [x] API клиент создан
- [x] Типы обновлены
- [x] AuthContext создан
- [x] Хуки обновлены
- [ ] AuthModal создан
- [ ] Header обновлен
- [ ] QuestDetail интегрирован
- [ ] UserProfile интегрирован
- [ ] Protected Routes настроены
- [ ] Error Boundary добавлен
- [ ] Тестирование пройдено

### Интеграция
- [ ] CORS работает
- [ ] API вызовы проходят
- [ ] Аутентификация работает
- [ ] Весь flow протестирован
- [ ] Обработка ошибок настроена
- [ ] Loading states добавлены

---

## 📚 Документация

После завершения обновить:
- `README_DEPLOYMENT.md` - добавить раздел про разработку
- `VITE_USAGE.md` - обновить workflow
- Создать `FRONTEND_DEVELOPMENT.md` - гайд по разработке

---

## 🎯 Итоговый результат

После выполнения всех задач:

✅ **Полнофункциональное приложение**
- Регистрация и вход
- Просмотр и фильтрация квестов
- Начало и завершение квестов
- Лайки и прогресс
- Профиль пользователя

✅ **Production-ready**
- Обработка ошибок
- Loading states
- Защищенные роуты
- Оптимизированная производительность

✅ **Готово к деплою**
- Все интеграции работают
- Тесты пройдены
- Документация обновлена

---

**Общее время:** ~10 часов  
**Сложность:** Средняя  
**Приоритет:** Высокий
