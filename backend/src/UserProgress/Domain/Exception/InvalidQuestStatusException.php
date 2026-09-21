<?php

declare(strict_types=1);

namespace App\UserProgress\Domain\Exception;

use App\UserProgress\Domain\ValueObject\QuestStatus;
use Symfony\Component\Uid\Uuid;

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
}
