<?php

declare(strict_types=1);

namespace App\UserProgress\Infrastructure\Db;

use App\Platform\ValueObject\Platform;
use App\Shared\Domain\Event\DomainEventInterface;
use App\UserProgress\Domain\Event\AbstractUserQuestProgressEvent;
use App\UserProgress\Domain\Event\QuestAbandonedEvent;
use App\UserProgress\Domain\Event\QuestCompletedEvent;
use App\UserProgress\Domain\Event\QuestPausedEvent;
use App\UserProgress\Domain\Event\QuestResumedEvent;
use App\UserProgress\Domain\Event\QuestStartedEvent;
use App\UserProgress\Domain\Event\QuestStepCheckEvent;
use App\UserProgress\Domain\Repository\ProgressEventStoreInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\Uid\Uuid;

/**
 * Doctrine реализация Event Store для доменных событий UserQuestProgress.
 * 
 * Использует прямые SQL запросы через DBAL для максимальной производительности.
 * Хранит события в таблице domain_events_progress.
 */
final class DoctrineProgressEventStore implements ProgressEventStoreInterface
{
    public function __construct(
        private Connection $connection
    ) {
    }

    public function store(DomainEventInterface $event): void
    {
        // Проверяем, что событие относится к UserQuestProgress
        if (!$event instanceof AbstractUserQuestProgressEvent) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Event must be instance of %s, %s given',
                    AbstractUserQuestProgressEvent::class,
                    get_class($event)
                )
            );
        }

        $sql = '
            INSERT INTO domain_events_progress (
                aggregate_id,
                user_id,
                quest_id,
                event_type,
                event_data,
                platform,
                occurred_at
            ) VALUES (
                :aggregate_id,
                :user_id,
                :quest_id,
                :event_type,
                :event_data,
                :platform,
                :occurred_at
            )
        ';

        $this->connection->executeStatement($sql, [
            'aggregate_id' => $event->getAggregateId()->toRfc4122(),
            'user_id' => $event->getUserId()->toRfc4122(),
            'quest_id' => $event->getQuestId()->toRfc4122(),
            'event_type' => $event->getEventType(),
            'event_data' => json_encode($event->getEventData(), JSON_THROW_ON_ERROR),
            'platform' => json_encode($event->getPlatform()->toArray(), JSON_THROW_ON_ERROR),
            'occurred_at' => $event->getOccurredAt()->format('Y-m-d H:i:s'),
        ]);
    }

    public function getByAggregateId(Uuid $aggregateId): array
    {
        $sql = '
            SELECT * FROM domain_events_progress
            WHERE aggregate_id = :aggregate_id
            ORDER BY occurred_at ASC
        ';

        $rows = $this->connection->fetchAllAssociative($sql, [
            'aggregate_id' => $aggregateId->toRfc4122(),
        ]);

        return $this->hydrateEvents($rows);
    }

    public function getByUserId(Uuid $userId): array
    {
        $sql = '
            SELECT * FROM domain_events_progress
            WHERE user_id = :user_id
            ORDER BY occurred_at ASC
        ';

        $rows = $this->connection->fetchAllAssociative($sql, [
            'user_id' => $userId->toRfc4122(),
        ]);

        return $this->hydrateEvents($rows);
    }

    public function getByQuestId(Uuid $questId): array
    {
        $sql = '
            SELECT * FROM domain_events_progress
            WHERE quest_id = :quest_id
            ORDER BY occurred_at ASC
        ';

        $rows = $this->connection->fetchAllAssociative($sql, [
            'quest_id' => $questId->toRfc4122(),
        ]);

        return $this->hydrateEvents($rows);
    }

    /**
     * Гидратация событий из строк БД.
     * 
     * @param array<array<string, mixed>> $rows Строки из БД
     * @return array<DomainEventInterface> Список доменных событий
     */
    private function hydrateEvents(array $rows): array
    {
        $events = [];

        foreach ($rows as $row) {
            $events[] = $this->hydrateEvent($row);
        }

        return $events;
    }

    /**
     * Гидратация одного события из строки БД.
     * 
     * @param array<string, mixed> $row Строка из БД
     * @return DomainEventInterface Доменное событие
     * @throws \RuntimeException Если тип события неизвестен
     */
    private function hydrateEvent(array $row): DomainEventInterface
    {
        $aggregateId = Uuid::fromString($row['aggregate_id']);
        $userId = Uuid::fromString($row['user_id']);
        $questId = Uuid::fromString($row['quest_id']);
        $occurredAt = new \DateTimeImmutable($row['occurred_at']);
        $platformData = json_decode($row['platform'], true, 512, JSON_THROW_ON_ERROR);
        $platform = Platform::fromArray($platformData);
        $eventData = json_decode($row['event_data'], true, 512, JSON_THROW_ON_ERROR);
        $eventType = $row['event_type'];

        return match ($eventType) {
            QuestStartedEvent::class => new QuestStartedEvent(
                $aggregateId,
                $userId,
                $questId,
                $occurredAt,
                $platform
            ),
            QuestResumedEvent::class => new QuestResumedEvent(
                $aggregateId,
                $userId,
                $questId,
                $occurredAt,
                $platform
            ),
            QuestPausedEvent::class => new QuestPausedEvent(
                $aggregateId,
                $userId,
                $questId,
                $occurredAt,
                $platform
            ),
            QuestCompletedEvent::class => new QuestCompletedEvent(
                $aggregateId,
                $userId,
                $questId,
                $occurredAt,
                $platform
            ),
            QuestAbandonedEvent::class => new QuestAbandonedEvent(
                $aggregateId,
                $userId,
                $questId,
                $occurredAt,
                $platform
            ),
            QuestStepCheckEvent::class => new QuestStepCheckEvent(
                $aggregateId,
                $userId,
                $questId,
                $occurredAt,
                $platform,
                $eventData['client_latitude'],
                $eventData['client_longitude'],
                $eventData['distance_to_point'],
                $eventData['check_passed']
            ),
            default => throw new \RuntimeException(
                sprintf('Unknown event type: %s', $eventType)
            ),
        };
    }
}

