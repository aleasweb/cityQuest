<?php

declare(strict_types=1);

namespace App\Quest\Application\Service;

use App\Quest\Domain\Exception\QuestNotFoundException;
use App\Quest\Domain\Repository\QuestRepositoryInterface;
use Symfony\Component\Uid\Uuid;

final class QuestService
{
    public function __construct(
        private QuestRepositoryInterface $questRepository,
    ) {
    }

    /**
     * Получить данные квеста по ID.
     *
     * @return array<string, mixed>
     * @throws QuestNotFoundException
     */
    public function getQuestById(Uuid $id): array
    {
        $quest = $this->questRepository->findById($id);
        
        if ($quest === null) {
            throw QuestNotFoundException::withId($id);
        }
        
        return $quest->toArray();
    }
}
