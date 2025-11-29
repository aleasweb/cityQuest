<?php

declare(strict_types=1);

namespace App\UserProgress\Application\Service;

use App\Quest\Domain\Repository\QuestRepositoryInterface;
use App\UserProgress\Domain\Entity\UserQuestProgress;
use App\UserProgress\Domain\Exception\ActiveQuestExistsException;
use App\UserProgress\Domain\Exception\ProgressNotFoundException;
use App\UserProgress\Domain\Repository\UserQuestProgressRepositoryInterface;
use App\Quest\Domain\Exception\QuestNotFoundException;
use Symfony\Component\Uid\Uuid;

/**
 * Service for managing user quest progress
 */
class UserProgressService
{
    public function __construct(
        private readonly UserQuestProgressRepositoryInterface $progressRepository,
        private readonly QuestRepositoryInterface $questRepository
    ) {
    }

    /**
     * Start a quest for a user
     * 
     * @throws QuestNotFoundException If quest doesn't exist
     * @throws ActiveQuestExistsException If user already has an active quest
     */
    public function startQuest(Uuid $userId, Uuid $questId): UserQuestProgress
    {
        // Verify quest exists
        $quest = $this->questRepository->findById($questId);
        if ($quest === null) {
            throw QuestNotFoundException::withId($questId);
        }

        // Check if user already has an active quest
        $activeQuest = $this->progressRepository->findActiveByUserId($userId);
        if ($activeQuest !== null) {
            throw ActiveQuestExistsException::forUser($userId, $activeQuest->getQuestId());
        }

        // Check if progress already exists for this quest
        $existingProgress = $this->progressRepository->findByUserIdAndQuestId($userId, $questId);
        
        if ($existingProgress !== null) {
            // Resume from paused state
            $existingProgress->start();
            $this->progressRepository->save($existingProgress);
            
            return $existingProgress;
        }

        // Create new progress
        $progress = new UserQuestProgress($userId, $questId);
        $this->progressRepository->save($progress);

        return $progress;
    }

    /**
     * Pause an active quest
     * 
     * @throws ProgressNotFoundException If progress doesn't exist
     */
    public function pauseQuest(Uuid $userId, Uuid $questId): UserQuestProgress
    {
        $progress = $this->progressRepository->findByUserIdAndQuestId($userId, $questId);
        
        if ($progress === null) {
            throw ProgressNotFoundException::forUserAndQuest($userId, $questId);
        }

        $progress->pause();
        $this->progressRepository->save($progress);

        return $progress;
    }

    /**
     * Complete an active quest
     * 
     * @throws ProgressNotFoundException If progress doesn't exist
     */
    public function completeQuest(Uuid $userId, Uuid $questId): UserQuestProgress
    {
        $progress = $this->progressRepository->findByUserIdAndQuestId($userId, $questId);
        
        if ($progress === null) {
            throw ProgressNotFoundException::forUserAndQuest($userId, $questId);
        }

        $progress->complete();
        $this->progressRepository->save($progress);

        return $progress;
    }

    /**
     * Get user progress with filters
     * 
     * @param Uuid $userId
     * @param string|null $status Filter by status (active, paused, completed)
     * @param bool|null $liked Filter by liked status
     * @return array{data: array<array<string, mixed>>, meta: array<string, int>}
     */
    public function getUserProgress(Uuid $userId, ?string $status = null, ?bool $liked = null): array
    {
        $progressRecords = $this->progressRepository->findByUserIdWithFilters($userId, $status, $liked);

        // Build response with quest details
        $data = [];
        foreach ($progressRecords as $progress) {
            $quest = $this->questRepository->findById($progress->getQuestId());
            
            $progressData = [
                'questId' => (string) $progress->getQuestId(),
                'status' => $progress->getStatus()->value,
                'isLiked' => $progress->isLiked(),
                'completedAt' => $progress->getCompletedAt()?->format('Y-m-d H:i:s'),
                'startedAt' => $progress->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $progress->getUpdatedAt()->format('Y-m-d H:i:s'),
            ];

            // Include quest details if quest exists
            if ($quest !== null) {
                $progressData['quest'] = $quest->toArray();
            }

            $data[] = $progressData;
        }

        // Calculate metadata
        $allProgress = $this->progressRepository->findByUserId($userId);
        $meta = [
            'total' => count($allProgress),
            'completed' => count(array_filter($allProgress, fn($p) => $p->isCompleted())),
            'in_progress' => count(array_filter($allProgress, fn($p) => $p->isActive())),
            'paused' => count(array_filter($allProgress, fn($p) => $p->isPaused())),
            'liked' => count(array_filter($allProgress, fn($p) => $p->isLiked())),
        ];

        return [
            'data' => $data,
            'meta' => $meta,
        ];
    }

    /**
     * Get active quest for a user
     */
    public function getActiveQuest(Uuid $userId): ?UserQuestProgress
    {
        return $this->progressRepository->findActiveByUserId($userId);
    }
}
