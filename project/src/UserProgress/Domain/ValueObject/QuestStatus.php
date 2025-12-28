<?php

declare(strict_types=1);

namespace App\UserProgress\Domain\ValueObject;

enum QuestStatus: string
{
    case NEW = 'new';
    case ACTIVE = 'active';
    case PAUSED = 'paused';
    case COMPLETED = 'completed';

    public static function getValues(): array
    {
        return array_map(fn(self $status) => $status->value, self::cases());
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match ($this) {
            self::NEW => in_array($newStatus, [self::ACTIVE], true),
            self::ACTIVE => in_array($newStatus, [self::PAUSED, self::COMPLETED], true),
            self::PAUSED => in_array($newStatus, [self::ACTIVE], true),
            self::COMPLETED => false, // Completed quests cannot be changed
        };
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }

    public function isPaused(): bool
    {
        return $this === self::PAUSED;
    }
}
