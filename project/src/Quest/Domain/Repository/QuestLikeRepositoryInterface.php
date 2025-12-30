<?php

declare(strict_types=1);

namespace App\Quest\Domain\Repository;

use App\Quest\Domain\Entity\QuestLike;
use Symfony\Component\Uid\Uuid;

interface QuestLikeRepositoryInterface
{
    public function save(QuestLike $like): void;

    public function remove(QuestLike $like): void;

    public function findByUserAndQuest(Uuid $userId, Uuid $questId): ?QuestLike;

    public function countByQuest(Uuid $questId): int;

    /**
     * @return QuestLike[]
     */
    public function findByUser(Uuid $userId): array;

    /**
     * Get liked quest IDs from given list for specific user
     * 
     * @param Uuid $userId
     * @param Uuid[] $questIds
     * @return Uuid[] Array of liked quest IDs
     */
    public function findLikedQuestIds(Uuid $userId, array $questIds): array;
}

