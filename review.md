Готово. Вот полный отчёт.

---

# 🔍 Code Review проекта CityQuest

## 🔴 Критические баги

### 1. `checkQuestStep` — квест завершается преждевременно

```245:255:backend/src/UserProgress/Application/Service/UserProgressService.php
        if ($isLastStep) {
            $progress->complete();
            $nextStep = null;
        } else {
            $nextStep = $this->questStepRepository->findNextActiveByQuestAndNumber($questId, $currentStepNumber);
            if ($nextStep !== null) {
                $progress->complete();  // ← BUG: должен перейти к следующему шагу, а не завершить квест
            }
        }
```

Когда найден следующий шаг, вызывается `complete()` вместо `setCurrentStepNumber($nextStep->getNumber())`. Любой успешный чек (кроме последнего) завершает весь квест.

### 2. `check()` — вызов с неполными аргументами

```210:228:backend/src/UserProgress/Domain/Entity/UserQuestProgress.php
    public function check(): void
    {
        $this->apply(new QuestStepCheckEvent(
            $this->id,
            $this->userId,
            $this->questId
        ));
```

`QuestStepCheckEvent` требует 10 параметров (включая lat/lng/distance/checkPassed), а передаётся только 3. Вызовет PHP fatal error в рантайме.

### 3. `abandon()` — запутанная логика переходов

```190:210:backend/src/UserProgress/Domain/Entity/UserQuestProgress.php
    public function abandon(): void
    {
        $currentStatus = $this->getStatus();

        if (!$currentStatus->canTransitionTo(QuestStatus::NEW)) {   // проверяет переход в NEW
            throw InvalidQuestStatusException::cannotTransition(
                $this->questId,
                $currentStatus,
                QuestStatus::PAUSED   // ← сообщение об ошибке говорит про PAUSED
            );
        }
        // ...
    }
```

А в `mutate`:

```248:250:backend/src/UserProgress/Domain/Entity/UserQuestProgress.php
            case QuestAbandonedEvent::class:
                // @todo сбрасываем прогресс
                $this->status = QuestStatus::PAUSED->value;  // ← устанавливает PAUSED вместо удаления
```

Три разных статуса в одной операции (NEW / PAUSED / abandon). Семантика полностью сломана.

### 4. Рассинхронизация SQL-constraint с PHP-enum

SQL `check_status CHECK (status IN ('active', 'paused', 'completed'))`, а в PHP есть `case NEW = 'new'`. Попытка сохранить `UserQuestProgress` со статусом `new` вызовет constraint violation в PostgreSQL.

### 5. Таблица `domain_events_progress` отсутствует в init SQL

`DoctrineProgressEventStore` пишет в `domain_events_progress`, таблица создаётся только через миграцию, но **не включена в `cityquest.sql`**. При инициализации чистой БД event store не работает.

---

## 🟠 Проблемы безопасности

### 6. JWT-ключи и секреты в Git

`backend/config/jwt/private.pem`, `APP_SECRET`, `JWT_PASSPHRASE` — все закоммичены в `.env`. Несмотря на `.gitignore` для `*.pem`, ключ уже отслеживается. Это критически при публичном репо.

### 7. `secure: false` для JWT cookie

```25:26:backend/config/packages/lexik_jwt_authentication.yaml
            secure: false           # true for production with HTTPS
            httpOnly: true
```

И в logout:

```100:100:backend/src/User/Presentation/Controller/AuthController.php
                false, // secure - false for development, true for production with HTTPS
```

В production JWT cookie передаётся по HTTP. Нужен как минимум env-based переключатель.

### 8. Утечка деталей ошибок

```96:99:backend/src/UserProgress/Presentation/Controller/UserProgressController.php
            return $this->json([
                'error' => 'Failed to start quest',
                'message' => $e->getMessage()   // ← stack trace/internal details
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
```

Паттерн повторяется в каждом catch-блоке `UserProgressController`. `$e->getMessage()` может содержать SQL-запросы, пути к файлам и т.д.

### 9. Нет rate limiting на login

Endpoint `POST /api/auth/login` не имеет ограничения скорости — можно брутфорсить пароли.

---

## 🟡 Архитектурные проблемы

### 10. Нарушение DDD: Doctrine-аннотации на доменных сущностях

AGENTS.md: *"Domain Layer — без зависимостей на Symfony/Doctrine"*. Но **все** доменные сущности (`Quest`, `QuestStep`, `QuestLike`, `User`, `UserQuestProgress`) напрямую используют `#[ORM\...]`:

```10:12:backend/src/Quest/Domain/Entity/Quest.php
#[ORM\Entity]
#[ORM\Table(name: 'quests')]
class Quest
```

Это связывает доменный слой с инфраструктурой.

### 11. Анемичная доменная модель

`Quest` — чистый data bag: 15 сеттеров, ноль бизнес-логики, ноль инвариантов. Любой код может выставить `likesCount = -100` или `difficulty = 'banana'`. Нет валидации, нет фабричных методов.

### 12. `toArray()` на доменных сущностях

`Quest.toArray()`, `QuestStep.toArray()`, `UserQuestProgress.toArray()` — ответственность за сериализацию смешана с доменным слоем. Должна быть в отдельных DTO/Transformer.

### 13. Fat Controllers — логика в Presentation Layer

`QuestController.getQuests()` (строки 64–91) содержит бизнес-логику: маппинг городов, обогащение лайками, batch-запросы. Это должно быть в сервисном слое.

```64:91:backend/src/Quest/Presentation/Controller/QuestController.php
            // Получаем текущего пользователя для проверки лайков
            $securityUser = $this->getUser();
            $likedMap = [];
            if ($securityUser) {
                $user = $this->userRepository->findByUsername($securityUser->getUserIdentifier());
                if ($user) {
                    $questIds = array_map(
                        fn($quest) => \Symfony\Component\Uid\Uuid::fromString($quest['id']),
                        $result['data']
                    );
                    $likedMap = $this->questLikeService->getLikedStatusMap($user->getId(), $questIds);
                }
            }
            $cities = $this->getParameter('app.cities');
            foreach ($result['data'] as &$quest) {
                // ...
            }
```

Эта логика **дублируется** в `getQuests()` и `getNearbyQuests()`.

### 14. Дублирование `getQuestById`

- `QuestService.getQuestById()` — выбрасывает исключение при ненахождении
- `QuestListService.getQuestById()` — возвращает `null`

Два метода с одинаковым именем и разным поведением в разных сервисах.

### 15. `Quest.type` — строковый литерал вместо существующего enum

```54:55:backend/src/Quest/Domain/Entity/Quest.php
    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'linear'])]
    private string $type = 'linear';
```

Enum `QuestType` уже существует, но не используется.

### 16. `QuestStep.status` — magic numbers

Статус шага хранится как `int` (0/1) без enum. Нет читаемости, нет типобезопасности.

### 17. Мутация события (immutability violation)

```67:70:backend/src/UserProgress/Domain/Event/AbstractUserQuestProgressEvent.php
    public function withPlatform(Platform $platform): self
    {
        $this->platform = $platform;
        return $this;
    }
```

Доменные события должны быть immutable. Метод `withPlatform` мутирует объект in-place.

### 18. Не-`final` классы

По конвенции: *"`final` классы по умолчанию"*. Но `Quest`, `QuestStep`, `User`, `QuestListService`, `UserProgressService`, все контроллеры кроме `QuestStepController` — не `final`.

---

## 🟡 Проблемы производительности

### 19. N+1 в `findNearby`

```135:142:backend/src/Quest/Infrastructure/Db/DoctrineQuestRepository.php
        $quests = [];
        foreach ($rows as $row) {
            $quest = $this->entityManager->find(Quest::class, Uuid::fromString($row['id']));
            if ($quest !== null) {
                $quests[] = $quest;
            }
        }
```

Raw SQL → массив ID → по одному `SELECT` на каждый квест. На 100 результатов = 101 запрос.

### 20. N+1 в `getUserProgress` и `formatQuestProgress`

```140:142:backend/src/UserProgress/Application/Service/UserProgressService.php
        foreach ($progressRecords as $progress) {
            $quest = $this->questRepository->findById($progress->getQuestId());
```

и

```109:109:backend/src/User/Application/Service/ProfileService.php
        $quest = $this->questRepository->findById($progress->getQuestId());
```

### 21. Двойная выборка для мета-данных

```161:163:backend/src/UserProgress/Application/Service/UserProgressService.php
        $allProgress = $this->progressRepository->findByUserId($userId);
        $likedQuests = $this->questLikeService->getLikedQuests($userId);
```

Уже загрузили отфильтрованные записи, но снова загружаем ВСЁ для подсчёта статистики. Нужны `COUNT` запросы с группировкой.

### 22. Haversine без PostGIS

`findNearby` считает расстояние в SQL для каждой строки — full table scan. С PostGIS и пространственным индексом было бы на порядки быстрее.

### 23. Race condition в `toggleLike`

```39:48:backend/src/Quest/Application/Service/QuestLikeService.php
            $this->likeRepository->remove($existingLike);
            $this->questRepository->decrementLikesCount($questId);
            $newCount = $this->likeRepository->countByQuest($questId);
```

Три отдельных запроса без транзакции. Два параллельных лайка могут привести к некорректному `likesCount`.

---

## 🟡 Проблемы Frontend

### 24. Zod-схемы объявлены, но не используются

```78:80:frontend/web/src/shared/api.ts
    const response = await apiRequest<ApiResponse<Quest[]>>(
      `/quests${query ? `?${query}` : ''}`
    );
```

`QuestSchema` существует, но только `getQuestStep` вызывает `.parse()`. Все остальные эндпоинты просто приводят тип без валидации.

### 25. `window.location.reload()` вместо обновления состояния

```104:108:frontend/web/src/react-app/pages/QuestDetail.tsx
      showToast('Квест успешно запущен!', 'success');
      setTimeout(() => {
        window.location.reload();
      }, 1000);
```

Повторяется при start, pause, complete. Убивает SPA-опыт — нужно обновлять локальный стейт.

### 26. Обработка ошибок через string matching

```77:81:frontend/web/src/react-app/pages/QuestDetail.tsx
      if (err?.message?.includes('401')) {
        showToast('Требуется авторизация', 'error');
      } else if (err?.message?.includes('403')) {
```

Хрупкий паттерн. Нужно парсить HTTP-статус в `apiRequest` и выбрасывать типизированные ошибки.

### 27. Несогласованный API envelope

- `getQuests` → `{data: [...], meta: {...}}`
- `register` → `{message, user}`
- `getCurrentUser` → `{data: {user: {...}}}`
- `toggleLike` → `{message, data: {liked, likesCount}}`

AGENTS.md определяет `{ "data": ..., "meta"?: ... }`, но половина эндпоинтов отклоняется.

### 28. Несуществующий маршрут `/progress`

```158:160:frontend/web/src/react-app/pages/UserProfile.tsx
                onClick={() => navigate('/progress')}
```

Этот маршрут не определён в `App.tsx`. Нажатие перенаправит на пустую страницу.

### 29. Несовпадение типов Frontend/Backend

Frontend `UserProgressSchema` ожидает snake_case (`quest_id`, `is_liked`, `started_at`), а backend `UserQuestProgress.toArray()` возвращает camelCase (`questId`, `completedAt`, `createdAt`). Данные не пройдут Zod-валидацию, если её включить.

### 30. `isLiked` не возвращается в `getPublicProfileWithQuestHistory`

Frontend-тип `QuestProgressItem` использует `isLiked`, но `ProfileService.formatQuestProgress()` это поле не включает.

---

## 🔵 Инфраструктура / DevOps

### 31. Nginx `add_header` override

```22:28:docker/nginx/conf.d/default.conf
    location /s3/ {
        alias /app/public/s3/;
        add_header Cache-Control "public, immutable";
        add_header X-Debug-Location "s3-images";
    }
```

В Nginx `add_header` в дочернем `location` **полностью заменяет** родительские заголовки. Все security-заголовки (CSP, X-Frame-Options, etc.) не применяются к `/s3/`.

### 32. `pgadmin` в production compose

Сервис `pgadmin` с дефолтным паролем `pgadmin` включён безусловно. Должен быть в отдельном `compose.override.yaml` для dev.

### 33. Нет healthcheck для php-fpm и nginx

Только `db` имеет healthcheck. При падении php-fpm контейнер не перезапустится.

### 34. Build-артефакты `backend/frontend/dist/`

30+ файлов с хешами в имени неотслеживаемы, но захламляют `git status`. Нужно добавить в `.gitignore`.

---

## Резюме по приоритетам

| Приоритет | Кол-во | Что делать |
|-----------|--------|------------|
| 🔴 Критические баги | 5 | Исправить немедленно — `checkQuestStep` ломает геймплей, `check()` вызывает fatal, init SQL неполный |
| 🟠 Безопасность | 4 | Убрать секреты из git, `secure: true` для prod cookies, скрыть `$e->getMessage()` |
| 🟡 Архитектура | 9 | Вынести ORM из домена, убрать `toArray()` из сущностей, финализировать классы |
| 🟡 Производительность | 5 | Устранить N+1, добавить транзакции, рассмотреть PostGIS |
| 🟡 Frontend | 7 | Убрать `window.location.reload`, включить Zod-валидацию, типизировать ошибки |
| 🔵 Infra | 4 | Nginx headers, отделить pgadmin, healthchecks |