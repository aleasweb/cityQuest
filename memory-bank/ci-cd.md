# CI/CD & Deployment - CityQuest

> **Инструкции по развертыванию и безопасности приложения**

## 🔐 КРИТИЧЕСКИ ВАЖНО: JWT Ключи в Production

### ⚠️ ПРЕДУПРЕЖДЕНИЕ

**JWT ключи в репозитории предназначены ТОЛЬКО для разработки!**

Текущие ключи находятся в:
```
project/config/jwt/private.pem
project/config/jwt/public.pem
```

**❌ НЕ используйте эти ключи в production!**
**❌ Эти ключи должны быть в .gitignore (уже добавлены)!**

---

## 🔑 Генерация JWT ключей для Production

### Шаг 1: Генерация новых ключей на production сервере

**На production сервере выполните:**

```bash
# Вариант 1: Через Symfony команду (рекомендуется)
php bin/console lexik:jwt:generate-keypair --overwrite

# Вариант 2: Вручную через OpenSSL
mkdir -p config/jwt
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
```

**Важно:**
- Для production рекомендуется использовать **4096-битные ключи** (вместо 2048 для dev)
- Используйте **сложный passphrase** (не менее 32 символов)
- **Сохраните passphrase** в безопасном месте (password manager, vault)

### Шаг 2: Установка правильных прав доступа

```bash
# Установить владельца (замените www-data на вашего пользователя веб-сервера)
chown www-data:www-data config/jwt/*.pem

# Установить права доступа
chmod 600 config/jwt/private.pem  # Только чтение для владельца
chmod 644 config/jwt/public.pem   # Чтение для всех, запись для владельца

# Убедиться, что директория защищена
chmod 700 config/jwt
```

### Шаг 3: Настройка переменных окружения

Обновите `.env.local` на production сервере:

```bash
# В .env.local (НЕ коммитить в репозиторий!)
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=your_very_strong_passphrase_here_min_32_chars

# Время жизни токена (по умолчанию 3600 секунд = 1 час)
JWT_TOKEN_TTL=3600
```

**Безопасность passphrase:**

Вместо хранения passphrase в .env, используйте:

1. **Secrets Manager** (AWS Secrets Manager, Google Secret Manager)
2. **Environment Variables** на уровне сервера
3. **HashiCorp Vault**
4. **Kubernetes Secrets** (если используете K8s)

Пример для Docker Secrets:

```yaml
# docker-compose.prod.yml
services:
  php-fpm:
    environment:
      JWT_PASSPHRASE: /run/secrets/jwt_passphrase
    secrets:
      - jwt_passphrase

secrets:
  jwt_passphrase:
    external: true
```

### Шаг 4: Проверка работы новых ключей

```bash
# Очистить кеш
php bin/console cache:clear --env=prod

# Протестировать генерацию токена
curl -X POST https://your-domain.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'

# Должен вернуть JWT токен
```

---

## 📁 Структура файлов безопасности

### Файлы, которые НЕ должны быть в Git:

```
project/
├── .env.local              # ❌ Не коммитить
├── .env.production         # ❌ Не коммитить
├── config/
│   └── jwt/
│       ├── private.pem     # ❌ Не коммитить
│       └── public.pem      # ❌ Не коммитить
```

### Проверка .gitignore:

Убедитесь, что `.gitignore` содержит:

```gitignore
# JWT Keys
/config/jwt/*.pem

# Environment files
.env.local
.env.local.php
.env.*.local
.env.production
```

---

## 🚀 CI/CD Pipeline

### GitHub Actions (пример)

```yaml
# .github/workflows/deploy.yml
name: Deploy to Production

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          
      - name: Install dependencies
        run: |
          cd project
          composer install --no-dev --optimize-autoloader
          
      - name: Run tests
        run: |
          cd project
          php bin/phpunit
          
      - name: Deploy to server
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.PRODUCTION_HOST }}
          username: ${{ secrets.PRODUCTION_USER }}
          key: ${{ secrets.SSH_PRIVATE_KEY }}
          script: |
            cd /var/www/cityquest
            git pull origin main
            cd project
            composer install --no-dev --optimize-autoloader
            
            # НЕ генерировать ключи при каждом деплое!
            # Ключи должны быть уже сгенерированы и храниться на сервере
            
            php bin/console cache:clear --env=prod
            php bin/console doctrine:migrations:migrate --no-interaction
```

### GitLab CI (пример)

```yaml
# .gitlab-ci.yml
stages:
  - test
  - deploy

test:
  stage: test
  image: php:8.3-cli
  script:
    - cd project
    - composer install
    - php bin/phpunit
    
deploy_production:
  stage: deploy
  only:
    - main
  script:
    - 'command -v ssh-agent >/dev/null || ( apt-get update -y && apt-get install openssh-client -y )'
    - eval $(ssh-agent -s)
    - echo "$SSH_PRIVATE_KEY" | tr -d '\r' | ssh-add -
    - ssh -o StrictHostKeyChecking=no $DEPLOY_USER@$DEPLOY_HOST "
        cd /var/www/cityquest &&
        git pull origin main &&
        cd project &&
        composer install --no-dev --optimize-autoloader &&
        php bin/console cache:clear --env=prod &&
        php bin/console doctrine:migrations:migrate --no-interaction
      "
```

---

## 🔄 Процесс первичного развертывания Production

### 1. Подготовка сервера

```bash
# SSH на production сервер
ssh user@your-production-server.com

# Клонировать репозиторий
cd /var/www
git clone https://github.com/your-org/cityquest.git
cd cityquest/project

# Установить зависимости
composer install --no-dev --optimize-autoloader
```

### 2. Генерация JWT ключей (только первый раз!)

```bash
# Генерировать ключи с сильным passphrase
php bin/console lexik:jwt:generate-keypair

# Когда запросит passphrase, введите сложный пароль
# Сохраните passphrase в безопасном месте!
```

### 3. Настройка окружения

```bash
# Создать .env.local для production
cat > .env.local << 'EOF'
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=generate_random_32_chars_secret_here

DATABASE_URL="postgresql://username:password@localhost:5432/cityquest?serverVersion=16&charset=utf8"

JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=your_generated_passphrase_here
JWT_TOKEN_TTL=3600

CORS_ALLOW_ORIGIN='^https?://(localhost|127\.0\.0\.1|your-domain\.com)(:[0-9]+)?$'
EOF

# Установить права
chmod 600 .env.local
```

### 4. Настройка прав доступа

```bash
# Владелец файлов - пользователь веб-сервера
chown -R www-data:www-data /var/www/cityquest

# Права на директории
find /var/www/cityquest -type d -exec chmod 755 {} \;

# Права на файлы
find /var/www/cityquest -type f -exec chmod 644 {} \;

# Специальные права для JWT ключей
chmod 700 /var/www/cityquest/project/config/jwt
chmod 600 /var/www/cityquest/project/config/jwt/private.pem
chmod 644 /var/www/cityquest/project/config/jwt/public.pem

# Права на var директорию (кеш, логи)
chmod -R 777 /var/www/cityquest/project/var
```

### 5. Применение миграций

```bash
# Выполнить миграции базы данных
php bin/console doctrine:migrations:migrate --env=prod --no-interaction
```

### 6. Очистка и прогрев кеша

```bash
# Очистить кеш
php bin/console cache:clear --env=prod

# Прогреть кеш (опционально)
php bin/console cache:warmup --env=prod
```

### 7. Проверка работы

```bash
# Проверить health check
curl https://your-domain.com/health

# Проверить JWT аутентификацию
curl -X POST https://your-domain.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"test_password"}'
```

---

## 🔄 Ротация JWT ключей

### Когда нужно ротировать ключи:

- **Плановая ротация:** каждые 90-180 дней
- **Компрометация:** немедленно при подозрении на утечку
- **Смена команды:** при увольнении сотрудников с доступом
- **Инциденты безопасности:** при любых подозрительных активностях

### Процесс ротации (Zero-Downtime):

#### Шаг 1: Подготовка новых ключей

```bash
# Создать backup старых ключей
cp config/jwt/private.pem config/jwt/private.pem.backup
cp config/jwt/public.pem config/jwt/public.pem.backup

# Создать временную директорию для новых ключей
mkdir -p config/jwt/new
cd config/jwt/new

# Сгенерировать новые ключи
openssl genpkey -out private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in private.pem -out public.pem -pubout
```

#### Шаг 2: Настройка multiple ключей

Обновите `config/packages/lexik_jwt_authentication.yaml`:

```yaml
lexik_jwt_authentication:
    secret_key: '%env(resolve:JWT_SECRET_KEY)%'
    public_key: '%env(resolve:JWT_PUBLIC_KEY)%'
    pass_phrase: '%env(JWT_PASSPHRASE)%'
    user_id_claim: email
    
    # Добавить дополнительные публичные ключи для валидации старых токенов
    additional_public_keys:
        key1: '%kernel.project_dir%/config/jwt/public.pem.backup'
```

#### Шаг 3: Переключение на новые ключи

```bash
# Заменить основные ключи
mv config/jwt/new/private.pem config/jwt/private.pem
mv config/jwt/new/public.pem config/jwt/public.pem

# Обновить passphrase в .env.local
# JWT_PASSPHRASE=new_passphrase

# Очистить кеш
php bin/console cache:clear --env=prod
```

#### Шаг 4: Период перехода (grace period)

- Оставить `additional_public_keys` на **24-48 часов**
- Это позволит старым токенам продолжать работать
- Новые токены будут генерироваться с новым ключом

#### Шаг 5: Завершение ротации

```bash
# Удалить старые ключи после grace period
rm config/jwt/private.pem.backup
rm config/jwt/public.pem.backup

# Удалить additional_public_keys из конфигурации
# Очистить кеш
php bin/console cache:clear --env=prod
```

---

## 📊 Мониторинг и безопасность

### Логирование

Настройте логирование неудачных попыток аутентификации:

```yaml
# config/packages/monolog.yaml
when@prod:
    monolog:
        handlers:
            security:
                type: stream
                path: '%kernel.logs_dir%/security.log'
                level: warning
                channels: ['security']
```

### Мониторинг метрик

Следите за:
- Количеством неудачных попыток входа
- Использованием JWT токенов
- Подозрительными паттернами (брутфорс, необычное время доступа)

### Rate Limiting

Рекомендуется настроить rate limiting для `/api/auth/login`:

```yaml
# config/packages/rate_limiter.yaml
framework:
    rate_limiter:
        login:
            policy: 'sliding_window'
            limit: 5
            interval: '15 minutes'
```

---

## 📋 Чеклист безопасности Production

### Перед развертыванием:

- [ ] Новые JWT ключи сгенерированы на production сервере
- [ ] Passphrase сохранен в безопасном месте
- [ ] Права доступа к ключам настроены (600 для private, 644 для public)
- [ ] `.env.local` создан с production настройками
- [ ] `.env.local` НЕ в Git репозитории
- [ ] `.gitignore` содержит `config/jwt/*.pem`
- [ ] `APP_DEBUG=0` в production
- [ ] `APP_ENV=prod` в production
- [ ] CORS настроен правильно
- [ ] HTTPS настроен (обязательно!)
- [ ] Database credentials безопасны

### После развертывания:

- [ ] Тест успешной генерации JWT токена
- [ ] Тест валидации JWT токена
- [ ] Тест отказа при невалидном токене (401)
- [ ] Логи проверены на ошибки
- [ ] Мониторинг настроен
- [ ] Rate limiting работает
- [ ] Backup ключей создан и сохранен отдельно

---

## 🆘 Troubleshooting

### Проблема: "Invalid credentials" после смены ключей

**Решение:**
```bash
# Очистить кеш
php bin/console cache:clear --env=prod

# Проверить права доступа
ls -la config/jwt/

# Проверить passphrase в .env.local
```

### Проблема: "Cannot read private key"

**Решение:**
```bash
# Проверить права доступа
chmod 600 config/jwt/private.pem

# Проверить владельца
chown www-data:www-data config/jwt/private.pem

# Проверить, что файл не поврежден
openssl pkey -in config/jwt/private.pem -check
```

### Проблема: Старые токены не работают после ротации

**Решение:**
- Добавить старый публичный ключ в `additional_public_keys`
- Подождать истечения старых токенов (TTL)
- Или инвалидировать все токены через blacklist

---

## 📚 Дополнительные ресурсы

- [LexikJWTAuthenticationBundle Documentation](https://github.com/lexik/LexikJWTAuthenticationBundle/blob/2.x/Resources/doc/index.rst)
- [Symfony Security Best Practices](https://symfony.com/doc/current/security.html)
- [JWT Best Practices](https://tools.ietf.org/html/rfc8725)
- [OWASP Authentication Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html)

---

**Последнее обновление:** 2025-10-25  
**Автор:** CityQuest Development Team  
**Версия:** 1.0

