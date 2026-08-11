<?php

declare(strict_types=1);

namespace App\Quest\Domain\Exception;

use DomainException;
use Symfony\Component\Uid\Uuid;

class ActiveQuestNotFoundException extends DomainException
{
    public static function withUserId(Uuid $userId): self
    {
        return new self(sprintf('No active quest for user %s', (string) $userId));
    }
}
