<?php

declare(strict_types=1);

namespace App\UserProgress\Domain\Event;

/**
 * Событие возобновления квеста пользователем.
 */
final class QuestResumedEvent extends AbstractUserQuestProgressEvent
{
    public function getEventData(): array
    {
        return [];
    }
}

