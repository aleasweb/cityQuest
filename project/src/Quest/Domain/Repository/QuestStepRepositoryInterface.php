<?php

declare(strict_types=1);

namespace App\Quest\Domain\Repository;

use App\Quest\Domain\Entity\QuestStep;
use Symfony\Component\Uid\Uuid;

interface QuestStepRepositoryInterface
{
    public function findByQuestAndNumber(Uuid $questId, int $number): ?QuestStep;

    public function findFirstActiveByQuest(Uuid $questId): ?QuestStep;

    public function findNextActiveByQuestAndNumber(Uuid $questId, int $currentNumber): ?QuestStep;

    public function countActiveByQuest(Uuid $questId): int;

    public function isLastActiveStep(Uuid $questId, int $stepNumber): bool;

    public function save(QuestStep $questStep): void;
}
