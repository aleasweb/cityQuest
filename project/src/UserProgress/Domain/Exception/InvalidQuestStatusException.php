<?php

declare(strict_types=1);

namespace App\UserProgress\Domain\Exception;

use App\UserProgress\Domain\ValueObject\QuestStatus;
use Symfony\Component\Uid\Uuid;

/**
 * Exception thrown when trying to perform invalid status transition
 */
class InvalidQuestStatusException extends \DomainException
{
    public static function cannotTransition(Uuid $questId, QuestStatus $currentStatus, QuestStatus $newStatus): self
    {
        return new self(sprintf(
            'Cannot transition quest %s from status "%s" to "%s"',
            $questId->toRfc4122(),
            $currentStatus->value,
            $newStatus->value
        ));
    }

    public static function cannotPause(Uuid $questId, QuestStatus $currentStatus): self
    {
        return new self(sprintf(
            'Only active quests can be paused. Quest %s has status "%s"',
            $questId->toRfc4122(),
            $currentStatus->value
        ));
    }

    public static function cannotComplete(Uuid $questId, QuestStatus $currentStatus): self
    {
        return new self(sprintf(
            'Only active quests can be completed. Quest %s has status "%s"',
            $questId->toRfc4122(),
            $currentStatus->value
        ));
    }
}
