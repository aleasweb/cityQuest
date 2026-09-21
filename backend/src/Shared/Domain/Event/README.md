# Shared Event Sourcing Infrastructure

Базовая инфраструктура для реализации Event Sourcing паттерна в проекте CityQuest.

## Компоненты

### 1. DomainEventInterface

Базовый интерфейс для всех доменных событий в системе.

**Назначение:** Определяет контракт для доменных событий, которые представляют значимые изменения состояния в домене.

**Методы:**
- `getAggregateId(): Uuid` - ID агрегата, сгенерировавшего событие
- `getOccurredAt(): \DateTimeImmutable` - Время возникновения события
- `getEventType(): string` - FQCN класса события
- `getPlatform(): array` - Данные о платформе (web/iOS/Android)
- `getEventData(): array` - Дополнительные данные события

**Пример использования:**

```php
class QuestStartedEvent implements DomainEventInterface
{
    public function __construct(
        private Uuid $aggregateId,
        private Uuid $userId,
        private Uuid $questId,
        private \DateTimeImmutable $occurredAt,
        private array $platform
    ) {}
    
    public function getAggregateId(): Uuid
    {
        return $this->aggregateId;
    }
    
    public function getEventData(): array
    {
        return []; // Пустой для QuestStartedEvent
    }
    
    // ... остальные методы
}
```

### 2. RecordsEvents Trait

Trait для агрегатов, поддерживающих генерацию доменных событий.

**Назначение:** Предоставляет механизм накопления событий в агрегате с последующим извлечением для персистентности.

**Методы:**
- `pull(): array` - Извлечь накопленные события и очистить список
- `apply(DomainEventInterface $event): void` - Записать событие (protected)

**Пример использования:**

```php
class UserQuestProgress
{
    use RecordsEvents;
    
    public function start(\DateTimeImmutable $startedAt, array $platform): void
    {
        // 1. Изменить состояние
        $this->status = QuestStatus::IN_PROGRESS;
        $this->startedAt = $startedAt;
        
        // 2. Записать событие
        $this->apply(new QuestStartedEvent(
            $this->id,
            $this->userId,
            $this->questId,
            $startedAt,
            $platform
        ));
    }
}
```

**Извлечение событий в сервисе:**

```php
class UserProgressService
{
    public function startQuest(User $user, Quest $quest, array $platform): UserQuestProgress
    {
        // 1. Создать/обновить агрегат
        $progress = $this->repository->findOrCreate($user, $quest);
        $progress->start(new \DateTimeImmutable(), $platform);
        
        // 2. Сохранить агрегат
        $this->repository->save($progress);
        
        // 3. Извлечь и сохранить события
        $events = $progress->pull();
        foreach ($events as $event) {
            $this->eventStore->store($event);
        }
        
        return $progress;
    }
}
```

## Архитектурные решения

### 1. Синхронная запись событий
События записываются в той же транзакции, что и изменение состояния агрегата. Это обеспечивает консистентность данных.

### 2. Минимальная структура Event Store
События хранятся в простой таблице без `id` и `occurred_at`, что упрощает схему и снижает overhead.

### 3. Платформа-агностичность
`DomainEventInterface` не привязан к конкретному агрегату, что позволяет использовать его для любых доменных событий в системе.

### 4. Отсутствие восстановления из событий
Агрегаты не восстанавливаются из событий (no Event Sourcing в чистом виде). События используются только для аналитики и аудита.

## Тестирование

Unit тесты: `tests/Shared/Domain/Event/RecordsEventsTest.php`

**Покрытие:**
- ✅ Запись события в коллекцию
- ✅ Извлечение событий и очистка коллекции
- ✅ Пустой массив при отсутствии событий
- ✅ Множественные события

**Запуск тестов:**

```bash
docker-compose exec php-fpm php bin/phpunit tests/Shared/Domain/Event/RecordsEventsTest.php
```

## Расширение

Для добавления новых доменных событий:

1. Создайте класс события, реализующий `DomainEventInterface`
2. Используйте абстрактный базовый класс (например, `AbstractUserQuestProgressEvent`) для переиспользования общей логики
3. Добавьте `use RecordsEvents` в агрегат
4. Вызывайте `$this->apply($event)` при изменении состояния
5. Извлекайте события через `pull()` в сервисе

## См. также

- `src/UserProgress/Domain/Event/` - Конкретные события для UserQuestProgress
- `src/UserProgress/Domain/Repository/ProgressEventStoreInterface.php` - Event Store для UserProgress
- `memory-bank/tasks.md` (CQST-010) - Детальная документация задачи

