# Настройка CORS в Symfony для Frontend

## Установка NelmioCorsBundle

```bash
cd /Users/aleas/proj/cityQuest
make composer c='require nelmio/cors-bundle'
```

## Конфигурация

Создайте файл `project/config/packages/nelmio_cors.yaml`:

```yaml
nelmio_cors:
    defaults:
        origin_regex: true
        allow_origin:
            - 'http://localhost:5173'
            - 'http://127.0.0.1:5173'
        allow_methods:
            - GET
            - POST
            - PUT
            - PATCH
            - DELETE
            - OPTIONS
        allow_headers:
            - Content-Type
            - Authorization
            - Accept
            - Origin
        expose_headers:
            - Link
        max_age: 3600
    paths:
        '^/api/':
            allow_origin: ['*']
            allow_headers: ['*']
            allow_methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']
            max_age: 3600
```

## Проверка

После установки перезапустите контейнеры:

```bash
make restart
```

Проверьте CORS заголовки:

```bash
curl -X OPTIONS http://cityquest.test/api/quests \
  -H "Origin: http://localhost:5173" \
  -H "Access-Control-Request-Method: GET" \
  -v
```

Вы должны увидеть заголовки:
- `Access-Control-Allow-Origin: http://localhost:5173`
- `Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS`
- `Access-Control-Allow-Headers: Content-Type, Authorization`

## Альтернатива (без bundle)

Если не хотите устанавливать bundle, добавьте middleware в `config/packages/framework.yaml`:

```yaml
framework:
    # ...
    http_method_override: true
    
services:
    # ...
    App\Infrastructure\Middleware\CorsMiddleware:
        tags:
            - { name: kernel.event_listener, event: kernel.response, method: onKernelResponse }
```

И создайте класс `src/Infrastructure/Middleware/CorsMiddleware.php`:

```php
<?php

namespace App\Infrastructure\Middleware;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class CorsMiddleware
{
    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $response->headers->set('Access-Control-Max-Age', '3600');
        
        if ($event->getRequest()->getMethod() === 'OPTIONS') {
            $response->setStatusCode(Response::HTTP_NO_CONTENT);
        }
    }
}
```

## Production настройки

Для production используйте конкретные домены вместо `'*'`:

```yaml
nelmio_cors:
    defaults:
        allow_origin:
            - 'https://cityquest.com'
            - 'https://www.cityquest.com'
```
