<?php

declare(strict_types=1);

namespace App\Shared\Domain\Event;

use App\Platform\ValueObject\Platform;
use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

/**
 * Базовый интерфейс для всех доменных событий в системе.
 * 
 * Используется для реализации Event Sourcing паттерна.
 * Все доменные события должны реализовывать этот интерфейс.
 */
interface DomainEventInterface
{
    public function getAggregateId(): Uuid;

    public function getOccurredAt(): DateTimeImmutable;

    public function getEventType(): string;

    public function getPlatform(): ?Platform;

    /**
     * Получить дополнительные данные события.
     * 
     * Возвращает только специфичные данные события (без aggregateId, userId, questId, occurredAt).
     * Может возвращать пустой массив.
     * 
     * @return array<string, mixed> Дополнительные данные события
     */
    public function getEventData(): array;
}
