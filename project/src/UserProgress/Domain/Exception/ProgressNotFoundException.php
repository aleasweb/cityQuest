<?php

declare(strict_types=1);

namespace App\UserProgress\Domain\Exception;

use Symfony\Component\Uid\Uuid;

/**
 * Exception thrown when user quest progress is not found
 */
class ProgressNotFoundException extends \DomainException
{
    public static function forUserAndQuest(Uuid $userId, Uuid $questId): self
    {
        return new self(sprintf(
            'Progress not found for user %s and quest %s',
            $userId->toRfc4122(),
            $questId->toRfc4122()
        ));
    }

    public static function forId(Uuid $progressId): self
    {
        return new self(sprintf(
            'Progress with ID %s not found',
            $progressId->toRfc4122()
        ));
    }
}
