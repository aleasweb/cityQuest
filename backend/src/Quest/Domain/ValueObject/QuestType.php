<?php

declare(strict_types=1);

namespace App\Quest\Domain\ValueObject;

/**
 * Quest completion type enum
 * Defines how quest steps should be completed
 */
enum QuestType: string
{
    case LINEAR = 'linear';   // Sequential step completion (current implementation)
    case RANDOM = 'random';   // Steps can be completed in any order (future)
}
