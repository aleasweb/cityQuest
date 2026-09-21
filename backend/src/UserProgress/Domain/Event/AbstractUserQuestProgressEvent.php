<?php

declare(strict_types=1);

namespace App\UserProgress\Domain\Event;

use App\Platform\ValueObject\Platform;
use App\Shared\Domain\Event\DomainEventInterface;
use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

/**
 * Абстрактный базовый класс для всех событий UserQuestProgress агрегата.
 * 
 * Содержит общую функциональность для всех событий прогресса квеста:
 * - aggregateId (ID UserQuestProgress)
 * - userId (ID пользователя)
 * - questId (ID квеста)
 * - occurredAt (время события)
 * - platform (данные о платформе)
 * 
 * Дочерние классы должны реализовать только getEventData() для специфичных данных.
 */
abstract class AbstractUserQuestProgressEvent implements DomainEventInterface
{
    public function __construct(
        protected Uuid $aggregateId,
        protected Uuid $userId,
        protected Uuid $questId,
        protected ?DateTimeImmutable $occurredAt = null,
        protected ?Platform $platform = null
    ) {
        $this->occurredAt = $occurredAt ?? new DateTimeImmutable();
    }

    public function getAggregateId(): Uuid
    {
        return $this->aggregateId;
    }

    public function getUserId(): Uuid
    {
        return $this->userId;
    }

    public function getQuestId(): Uuid
    {
        return $this->questId;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function getEventType(): string
    {
        return static::class;
    }

    public function getPlatform(): ?Platform
    {
        return $this->platform;
    }

    public function withPlatform(Platform $platform): self
    {
        $this->platform = $platform;
        return $this;
    }

    /**
     * Получить дополнительные данные события.
     * 
     * Переопределяется в дочерних классах для специфичных данных.
     * Для большинства событий возвращает пустой массив.
     * 
     * @return array<string, mixed> Дополнительные данные события
     */
    abstract public function getEventData(): array;
}
