<?php

declare(strict_types=1);

namespace App\UserProgress\Domain\Event;

/**
 * Событие начала квеста пользователем.
 */
final class QuestStartedEvent extends AbstractUserQuestProgressEvent
{
    public function getEventData(): array
    {
        return [];
    }
}

