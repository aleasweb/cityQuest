<?php

declare(strict_types=1);

namespace App\Quest\Application\Service;

use App\Quest\Domain\Exception\ActiveQuestNotFoundException;
use App\Quest\Domain\Exception\QuestStepNotFoundException;
use App\Quest\Domain\Repository\QuestStepRepositoryInterface;
use App\UserProgress\Domain\Repository\UserQuestProgressRepositoryInterface;
use Symfony\Component\Uid\Uuid;

final class QuestStepService
{
    public function __construct(
        private readonly QuestStepRepositoryInterface $questStepRepository,
        private readonly UserQuestProgressRepositoryInterface $userProgressRepository
    ) {
    }

    /**
     * @return array<string, mixed> Step data
     */
    public function getStepForActiveQuest(Uuid $userId): array
    {
        $progress = $this->userProgressRepository->findActiveByUserId($userId);
        if (!$progress) {
            throw ActiveQuestNotFoundException::withUserId($userId);
        }

        $stepNumber = $progress->getCurrentStepNumber();
        if (!$stepNumber) {
            throw ActiveQuestNotFoundException::withUserId($userId);
        }

        $step = $this->questStepRepository->findByQuestAndNumber($progress->getQuestId(), $stepNumber);

        if ($step === null) {
            throw QuestStepNotFoundException::withQuestStepAndNumber($progress->getQuestId(), $stepNumber);
        }

        return $step->toArray();
    }
}
