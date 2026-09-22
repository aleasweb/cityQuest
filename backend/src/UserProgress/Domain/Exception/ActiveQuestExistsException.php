<?php

declare(strict_types=1);

namespace App\UserProgress\Domain\Exception;

use Symfony\Component\Uid\Uuid;

/**
 * Exception thrown when trying to start a quest while another quest is active
 */
class ActiveQuestExistsException extends \DomainException
{
    public static function forUser(Uuid $userId, Uuid $activeQuestId): self
    {
        return new self(sprintf(
            'User %s already has an active quest %s. Pause it before starting a new one.',
            $userId->toRfc4122(),
            $activeQuestId->toRfc4122()
        ));
    }
}
