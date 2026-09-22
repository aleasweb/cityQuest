<?php

declare(strict_types=1);

namespace App\Quest\Application\Service;

use App\Quest\Domain\Entity\QuestLike;
use App\Quest\Domain\Exception\QuestNotFoundException;
use App\Quest\Domain\Repository\QuestLikeRepositoryInterface;
use App\Quest\Domain\Repository\QuestRepositoryInterface;
use Symfony\Component\Uid\Uuid;

final class QuestLikeService
{
    public function __construct(
        private readonly QuestLikeRepositoryInterface $likeRepository,
        private readonly QuestRepositoryInterface $questRepository
    ) {
    }

    /**
     * Toggle like for a quest
     *
     * @throws QuestNotFoundException If quest doesn't exist
     * @return array{liked: bool, likesCount: int}
     */
    public function toggleLike(Uuid $userId, Uuid $questId): array
    {
        // Verify quest exists
        $quest = $this->questRepository->findById($questId);
        if ($quest === null) {
            throw QuestNotFoundException::withId($questId);
        }

        $existingLike = $this->likeRepository->findByUserAndQuest($userId, $questId);

        if ($existingLike) {
            // Unlike
            $this->likeRepository->remove($existingLike);
            
            // Update denormalized likes_count in Quest entity (DQL)
            $this->questRepository->decrementLikesCount($questId);
            $newCount = $this->likeRepository->countByQuest($questId);

            return [
                'liked' => false,
                'likesCount' => $newCount,
            ];
        }

        // Like
        $like = new QuestLike($userId, $questId);
        $this->likeRepository->save($like);
        
        // Update denormalized likes_count in Quest entity (DQL)
        $this->questRepository->incrementLikesCount($questId);
        $newCount = $this->likeRepository->countByQuest($questId);

        return [
            'liked' => true,
            'likesCount' => $newCount,
        ];
    }

    /**
     * Check if user has liked a quest
     */
    public function isLiked(Uuid $userId, Uuid $questId): bool
    {
        return $this->likeRepository->findByUserAndQuest($userId, $questId) !== null;
    }

    /**
     * Get all liked quests for a user
     *
     * @return QuestLike[]
     */
    public function getLikedQuests(Uuid $userId): array
    {
        return $this->likeRepository->findByUser($userId);
    }

    /**
     * Get liked status for multiple quests at once (batch operation)
     * 
     * @param Uuid $userId
     * @param Uuid[] $questIds
     * @return array<string, bool> Map of quest_id (string) => isLiked (bool)
     */
    public function getLikedStatusMap(Uuid $userId, array $questIds): array
    {
        if (empty($questIds)) {
            return [];
        }

        $likedQuestIds = $this->likeRepository->findLikedQuestIds($userId, $questIds);
        
        // Create a set for fast lookup
        $likedSet = [];
        foreach ($likedQuestIds as $likedId) {
            $likedSet[$likedId->toRfc4122()] = true;
        }
        
        // Build map for all quest IDs
        $map = [];
        foreach ($questIds as $questId) {
            $key = $questId->toRfc4122();
            $map[$key] = isset($likedSet[$key]);
        }
        
        return $map;
    }
}

