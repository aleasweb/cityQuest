<?php

declare(strict_types=1);

namespace App\UserProgress\Domain\Repository;

use App\Shared\Domain\Event\DomainEventInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Интерфейс Event Store для хранения доменных событий UserQuestProgress.
 * 
 * Предоставляет методы для записи и чтения событий из таблицы domain_events_progress.
 */
interface ProgressEventStoreInterface
{
    /**
     * Сохранить доменное событие в Event Store.
     */
    public function store(DomainEventInterface $event): void;

    /**
     * Получить все события для конкретного агрегата.
     *
     * @return array<DomainEventInterface> Список событий, отсортированных по occurred_at ASC
     */
    public function getByAggregateId(Uuid $aggregateId): array;

    /**
     * Получить все события для конкретного пользователя.
     *
     * @return array<DomainEventInterface> Список событий, отсортированных по occurred_at ASC
     */
    public function getByUserId(Uuid $userId): array;

    /**
     * Получить все события для конкретного квеста.
     *
     * @return array<DomainEventInterface> Список событий, отсортированных по occurred_at ASC
     */
    public function getByQuestId(Uuid $questId): array;
}
