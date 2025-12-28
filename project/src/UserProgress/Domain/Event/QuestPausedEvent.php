<?php

declare(strict_types=1);

namespace App\UserProgress\Domain\Event;

/**
 * Событие приостановки квеста пользователем.
 */
final class QuestPausedEvent extends AbstractUserQuestProgressEvent
{
    public function getEventData(): array
    {
        return [];
    }
}

