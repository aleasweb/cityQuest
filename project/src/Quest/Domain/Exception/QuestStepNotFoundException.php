<?php

declare(strict_types=1);

namespace App\Quest\Domain\Exception;

use DomainException;
use Symfony\Component\Uid\Uuid;

class QuestStepNotFoundException extends DomainException
{
    public static function withQuestStepAndNumber(Uuid $questId, int $stepNumber): self
    {
        return new self(sprintf('Not found step %s for quest "%s"', $stepNumber, (string) $questId));
    }
}
