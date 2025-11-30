# ✅ TODO - Финализация интеграции Frontend → Symfony

## 🔴 Критично (требуется для работы)

### 1. Настроить CORS в Symfony
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

```bash
make restart
```

**Проверка:**
```bash
curl -X OPTIONS http://cityquest.test/api/quests \
  -H "Origin: http://localhost:5173" \
  -H "Access-Control-Request-Method: GET" \
  -v | grep "Access-Control"
```

### 2. Установить зависимости Frontend
```bash
cd /Users/aleas/proj/cityQuest/frontend/web
npm install
```

### 3. Запустить и протестировать
```bash
npm run dev
# Открыть http://localhost:5173
# Проверить что квесты загружаются
```

## 🟡 Важно (для полной функциональности)

### 4. Обновить компоненты с аутентификацией

Файлы для обновления:
- `src/react-app/components/Header.tsx`
- `src/react-app/pages/UserProfile.tsx`
- `src/react-app/pages/AuthCallback.tsx` (можно удалить)

Заменить:
```typescript
// Старое
import { useAuth } from '@getmocha/users-service/react';

// Новое
import { useAuth } from '@/react-app/contexts/AuthContext';
```

### 5. Добавить endpoint `/api/cities` в Symfony

Создать метод в `QuestController.php`:
```php
#[Route('/api/cities', name: 'api_cities', methods: ['GET'])]
public function getCities(): JsonResponse
{
    $cities = $this->questListService->getDistinctCities();
    return $this->json(['data' => $cities]);
}
```

И метод в `QuestListService`:
```php
public function getDistinctCities(): array
{
    return $this->questRepository->findDistinctCities();
}
```

### 6. Тестирование основного flow

- [ ] Регистрация пользователя
- [ ] Вход в систему
- [ ] Просмотр списка квестов
- [ ] Фильтрация по городу
- [ ] Просмотр деталей квеста
- [ ] Лайк квеста (требует авторизации)
- [ ] Начать квест (требует авторизации)
- [ ] Завершить квест (требует авторизации)

## 🟢 Опционально (улучшения)

### 7. Удалить неиспользуемые файлы
```bash
cd /Users/aleas/proj/cityQuest/frontend/web
rm -rf src/worker
rm wrangler.jsonc
rm -rf migrations
```

### 8. Добавить обработку ошибок

Создать `src/react-app/components/ErrorBoundary.tsx`:
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

  componentDidCatch(error: Error, errorInfo: any) {
    console.error('Error caught by boundary:', error, errorInfo);
  }

  render() {
    if (this.state.hasError) {
      return (
        <div className="error-container">
          <h1>Что-то пошло не так</h1>
          <p>{this.state.error?.message}</p>
        </div>
      );
    }

    return this.props.children;
  }
}
```

### 9. Добавить Loading UI

Создать глобальный loading indicator для API запросов.

### 10. Добавить Refresh Token механизм

Для автоматического обновления JWT токена при истечении.

### 11. Добавить React Query

Для кеширования и оптимизации API запросов:
```bash
npm install @tanstack/react-query
```

### 12. Добавить E2E тесты

```bash
npm install -D playwright
npx playwright install
```

## 📋 Чеклист по статусу

### Backend
- [ ] CORS настроен
- [ ] Endpoint `/api/cities` добавлен
- [ ] JWT корректно работает
- [ ] Тесты проходят

### Frontend
- [x] API клиент создан
- [x] Типы обновлены
- [x] AuthContext создан
- [x] Хуки обновлены
- [x] Vite proxy настроен
- [ ] Компоненты обновлены
- [ ] Удалены неиспользуемые файлы
- [ ] Тесты написаны

### Интеграция
- [ ] CORS работает
- [ ] API вызовы проходят
- [ ] Аутентификация работает
- [ ] Весь flow протестирован

## 🚀 Порядок выполнения

1. **Сначала Backend** - настройте CORS, иначе ничего не будет работать
2. **Потом Frontend** - установите зависимости и запустите
3. **Затем тестирование** - проверьте основной flow
4. **Потом рефакторинг** - обновите компоненты
5. **В конце - чистка** - удалите ненужное и добавьте улучшения

## 💡 Быстрый старт (минимум для работы)

```bash
# 1. Backend CORS
cd /Users/aleas/proj/cityQuest
make composer c='require nelmio/cors-bundle'
# Создать config/packages/nelmio_cors.yaml (см. выше)
make restart

# 2. Frontend
cd frontend/web
npm install
npm run dev

# 3. Открыть http://localhost:5173
```

## 📞 Если что-то не работает

1. Проверьте консоль браузера (F12)
2. Проверьте Network tab на наличие CORS ошибок
3. Проверьте что Symfony API отвечает: `curl http://cityquest.test/api/health`
4. Проверьте логи Symfony: `make bash` → `tail -f var/log/dev.log`
