<?php

declare(strict_types=1);

namespace App\UserProgress\Domain\Event;

/**
 * Событие завершения квеста пользователем.
 */
final class QuestCompletedEvent extends AbstractUserQuestProgressEvent
{
    public function getEventData(): array
    {
        return [];
    }
}

