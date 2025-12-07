<?php

declare(strict_types=1);

namespace App\UserProgress\Application\Service;

use App\Quest\Domain\Exception\QuestNotFoundException;
use App\Quest\Domain\Repository\QuestRepositoryInterface;
use App\UserProgress\Domain\Entity\UserQuestProgress;
use App\UserProgress\Domain\Exception\QuestNotStartedException;
use App\UserProgress\Domain\Repository\UserQuestProgressRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Service for managing quest likes
 */
class QuestLikeService
{
    public function __construct(
        private readonly UserQuestProgressRepositoryInterface $progressRepository,
        private readonly QuestRepositoryInterface $questRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Toggle like for a quest
     * 
     * @throws QuestNotFoundException If quest doesn't exist
     * @throws QuestNotStartedException If quest hasn't been started
     * @return array{liked: bool, likesCount: int} Current like status and total likes count
     */
    public function toggleLike(Uuid $userId, Uuid $questId): array
    {
        // Verify quest exists
        $quest = $this->questRepository->findById($questId);
        if ($quest === null) {
            throw QuestNotFoundException::withId($questId);
        }

        // Check if quest has been started
        $progress = $this->progressRepository->findByUserIdAndQuestId($userId, $questId);
        if ($progress === null) {
            throw QuestNotStartedException::forQuest($questId);
        }

        // Use transaction to ensure consistency
        $this->entityManager->beginTransaction();
        
        try {
            // Toggle existing like
            $wasLiked = $progress->isLiked();
            $liked = $progress->toggleLike();
            $this->progressRepository->save($progress);
            
            // Update likes count
            if ($liked && !$wasLiked) {
                $this->questRepository->incrementLikesCount($questId);
            } elseif (!$liked && $wasLiked) {
                $this->questRepository->decrementLikesCount($questId);
            }
            
            $this->entityManager->commit();
            
            // Get updated quest to return current likes count
            $this->entityManager->clear();
            $updatedQuest = $this->questRepository->findById($questId);
            
            return [
                'liked' => $liked,
                'likesCount' => $updatedQuest?->getLikesCount() ?? 0,
            ];
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * Check if user can like a quest
     * User can like a quest only if it has been started (exists in user_quest_progress)
     */
    public function canLike(Uuid $userId, Uuid $questId): bool
    {
        $progress = $this->progressRepository->findByUserIdAndQuestId($userId, $questId);
        
        return $progress !== null;
    }

    /**
     * Check if user has liked a quest
     */
    public function isLiked(Uuid $userId, Uuid $questId): bool
    {
        $progress = $this->progressRepository->findByUserIdAndQuestId($userId, $questId);
        
        return $progress !== null && $progress->isLiked();
    }

    /**
     * Get all liked quests for a user
     * 
     * @return UserQuestProgress[]
     */
    public function getLikedQuests(Uuid $userId): array
    {
        return $this->progressRepository->findByUserIdWithFilters($userId, null, true);
    }
}
