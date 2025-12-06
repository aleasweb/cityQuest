# ⚡ Фаза 0: Quick Wins (~45 минут)

Простые задачи для очистки UI перед основной интеграцией.

---

## 0.1. Убрать переключение темы (15 мин)

### 1. Упростить ThemeContext

**Файл:** `frontend/web/src/react-app/contexts/ThemeContext.tsx`

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

### 2. Обновить Header.tsx

**Файл:** `frontend/web/src/react-app/components/Header.tsx`

Найти и удалить блок:
```typescript
{/* Theme Toggle */}
<button
  onClick={toggleTheme}
  className="..."
>
  {theme === 'light' ? (
    <Moon className="..." />
  ) : (
    <Sun className="..." />
  )}
</button>
```

Также удалить:
```typescript
const { theme, toggleTheme } = useTheme();
```

И импорт:
```typescript
import { Moon, Sun, ... } from 'lucide-react'; // Удалить Moon, Sun
```

---

## 0.2. Убрать поиск по названию (10 мин)

### 1. Обновить types.ts

**Файл:** `frontend/web/src/shared/types.ts`

```typescript
export const QuestFiltersSchema = z.object({
  city: z.string().optional(),
  difficulty: z.enum(['легкие', 'средние', 'сложные']).optional(),
  // Удалить эту строку:
  // search: z.string().optional()
});
```

### 2. Обновить api.ts

**Файл:** `frontend/web/src/shared/api.ts`

В методе `getQuests` удалить:
```typescript
// Удалить эту строку:
// if (filters?.search) params.append('search', filters.search);
```

### 3. Обновить Filters.tsx

**Файл:** `frontend/web/src/react-app/components/Filters.tsx`

Найти и удалить блок с поиском (обычно это input с placeholder "Поиск...").

---

## 0.3. Изображения из API (5 мин)

**Файл:** `frontend/web/src/react-app/components/QuestCard.tsx`

Найти `<img>` тег и обновить:

```typescript
<img 
  src={quest.imageUrl || '/placeholder.png'} 
  alt={quest.title}
  className="w-full h-48 object-cover"
  onError={(e) => {
    e.currentTarget.src = '/placeholder.png';
  }}
/>
```

---

## 0.4. Относительные URL (5 мин)

**Файл:** `frontend/web/src/shared/api.ts`

Изменить:
```typescript
// Было:
const API_URL = import.meta.env.VITE_API_URL || 'http://cityquest.test/api';

// Стало:
const API_URL = import.meta.env.VITE_API_URL || '/api';
```

---

## 0.5. Ширина карточек 400px (5 мин)

**Файл:** `frontend/web/src/react-app/components/QuestCard.tsx`

Обновить className главного div:

```typescript
export default function QuestCard({ quest, onClick }: Props) {
  return (
    <div 
      onClick={onClick}
      className="w-[400px] flex-shrink-0 bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow cursor-pointer overflow-hidden"
    >
      {/* остальной код */}
    </div>
  );
}
```

---

## ✅ Проверка

После выполнения всех задач:

```bash
# Пересобрать frontend
cd /Users/aleas/proj/cityQuest
./build-frontend-docker.sh

# Открыть и проверить
open http://cityquest.test
```

Должно быть:
- ✅ Нет кнопки переключения темы
- ✅ Нет поля поиска
- ✅ Изображения квестов отображаются
- ✅ Карточки 400px ширины
- ✅ Все работает на одном домене

---

## Время выполнения

| Задача | Время |
|--------|-------|
| 0.1. Убрать тему | 15 мин |
| 0.2. Убрать поиск | 10 мин |
| 0.3. Изображения | 5 мин |
| 0.4. Относительные URL | 5 мин |
| 0.5. Ширина 400px | 5 мин |
| **Пересборка** | 5 мин |
| **ИТОГО** | **45 мин** |

---

## Следующие шаги

После Фазы 0 переходите к:

**Фаза 1: CORS и аутентификация**

См. FRONTEND_API_INTEGRATION_PLAN.md
