<?php

declare(strict_types=1);

namespace App\UserProgress\Application\Service;

use App\Platform\Application\Service\PlatformResolver;
use App\Quest\Domain\Exception\QuestNotFoundException;
use App\Quest\Domain\Repository\QuestRepositoryInterface;
use App\UserProgress\Domain\Entity\UserQuestProgress;
use App\UserProgress\Domain\Event\AbstractUserQuestProgressEvent;
use App\UserProgress\Domain\Exception\ActiveQuestExistsException;
use App\UserProgress\Domain\Exception\ProgressNotFoundException;
use App\UserProgress\Domain\Repository\ProgressEventStoreInterface;
use App\UserProgress\Domain\Repository\UserQuestProgressRepositoryInterface;
use Symfony\Component\Uid\Uuid;

class UserProgressService
{
    public function __construct(
        private readonly UserQuestProgressRepositoryInterface $progressRepository,
        private readonly QuestRepositoryInterface $questRepository,
        private readonly ProgressEventStoreInterface $eventStore,
        private readonly PlatformResolver $platformResolver
    ) {
    }

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
            $existingProgress->resume();
            $this->progressRepository->save($existingProgress);
            $this->storeEvents($existingProgress);
            
            return $existingProgress;
        }

        // Create new progress
        $progress = new UserQuestProgress($userId, $questId);
        $progress->start();
        $this->progressRepository->save($progress);
        $this->storeEvents($progress);

        return $progress;
    }

    /**
     * @throws ProgressNotFoundException
     */
    public function pauseQuest(Uuid $userId, Uuid $questId): UserQuestProgress
    {
        $progress = $this->progressRepository->findByUserIdAndQuestId($userId, $questId);
        if ($progress === null) {
            throw ProgressNotFoundException::forUserAndQuest($userId, $questId);
        }

        $progress->pause();
        $this->progressRepository->save($progress);
        $this->storeEvents($progress);

        return $progress;
    }

    public function completeQuest(Uuid $userId, Uuid $questId): UserQuestProgress
    {
        $progress = $this->progressRepository->findByUserIdAndQuestId($userId, $questId);
        if ($progress === null) {
            throw ProgressNotFoundException::forUserAndQuest($userId, $questId);
        }

        $progress->complete();
        $this->progressRepository->save($progress);
        $this->storeEvents($progress);

        return $progress;
    }

    /**
     * @throws ProgressNotFoundException
     */
    public function abandonQuest(Uuid $userId, Uuid $questId): void
    {
        $progress = $this->progressRepository->findByUserIdAndQuestId($userId, $questId);
        
        if ($progress === null) {
            throw ProgressNotFoundException::forUserAndQuest($userId, $questId);
        }

        $progress->abandon();
        $this->progressRepository->save($progress);
        $this->storeEvents($progress);
    }

    /**
     * @return array{data: array<array<string, mixed>>, meta: array<string, int>}
     */
    public function getUserProgress(Uuid $userId, ?string $status = null): array
    {
        $progressRecords = $this->progressRepository->findByUserIdWithFilters($userId, $status);

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
            'completed' => count(array_filter($allProgress, fn($p) => $p->getStatus()->isCompleted())),
            'in_progress' => count(array_filter($allProgress, fn($p) => $p->getStatus()->isActive())),
            'paused' => count(array_filter($allProgress, fn($p) => $p->getStatus()->isPaused())),
            'liked' => count(array_filter($allProgress, fn($p) => $p->isLiked())),
        ];

        return [
            'data' => $data,
            'meta' => $meta,
        ];
    }


    public function getActiveQuest(Uuid $userId): ?UserQuestProgress
    {
        return $this->progressRepository->findActiveByUserId($userId);
    }

    /**
     * Извлечь и сохранить доменные события из агрегата.
     */
    private function storeEvents(UserQuestProgress $progress): void
    {
        $platform = $this->platformResolver->resolve();

        $events = $progress->pull();

        /** @var AbstractUserQuestProgressEvent $event */
        foreach ($events as $event) {
            $event->withPlatform($platform);
            $this->eventStore->store($event);
        }
    }
}
