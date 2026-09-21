<?php

declare(strict_types=1);

namespace App\UserProgress\Domain\Exception;

use Symfony\Component\Uid\Uuid;

/**
 * Exception thrown when trying to like a quest that hasn't been started
 */
class QuestNotStartedException extends \DomainException
{
    public static function forQuest(Uuid $questId): self
    {
        return new self(
            sprintf('Quest with ID %s must be started before it can be liked', $questId->toRfc4122())
        );
    }
}

