<?php

declare(strict_types=1);

namespace App\UserProgress\Domain\ValueObject;

/**
 * Quest status enum for user progress tracking
 */
enum QuestStatus: string
{
    case ACTIVE = 'active';
    case PAUSED = 'paused';
    case COMPLETED = 'completed';

    /**
     * Get all possible status values
     *
     * @return array<string>
     */
    public static function getValues(): array
    {
        return array_map(fn(self $status) => $status->value, self::cases());
    }

    /**
     * Check if status allows transition to another status
     */
    public function canTransitionTo(self $newStatus): bool
    {
        return match ($this) {
            self::ACTIVE => in_array($newStatus, [self::PAUSED, self::COMPLETED], true),
            self::PAUSED => in_array($newStatus, [self::ACTIVE], true),
            self::COMPLETED => false, // Completed quests cannot be changed
        };
    }

    /**
     * Check if this is an active status
     */
    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Check if this is a completed status
     */
    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }

    /**
     * Check if this is a paused status
     */
    public function isPaused(): bool
    {
        return $this === self::PAUSED;
    }
}
