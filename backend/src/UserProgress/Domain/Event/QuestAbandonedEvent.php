<?php

declare(strict_types=1);

namespace App\UserProgress\Domain\Event;

/**
 * Событие отказа от квеста пользователем.
 */
final class QuestAbandonedEvent extends AbstractUserQuestProgressEvent
{
    public function getEventData(): array
    {
        return [];
    }
}

