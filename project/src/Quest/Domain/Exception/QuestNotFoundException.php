<?php

declare(strict_types=1);

namespace App\Quest\Domain\Exception;

use Symfony\Component\Uid\Uuid;

class QuestNotFoundException extends \DomainException
{
    public static function withId(Uuid $id): self
    {
        return new self(sprintf('Quest with id "%s" not found', (string) $id));
    }
}
