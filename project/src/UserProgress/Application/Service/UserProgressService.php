<?php

declare(strict_types=1);

namespace App\UserProgress\Application\Service;

use App\Platform\Application\Service\PlatformResolver;
use App\Quest\Application\Service\QuestLikeService;
use App\Quest\Domain\Exception\QuestNotFoundException;
use App\Quest\Domain\Exception\QuestStepNotFoundException;
use App\Quest\Domain\Repository\QuestRepositoryInterface;
use App\Quest\Domain\Repository\QuestStepRepositoryInterface;
use App\Shared\Geo\Application\Service\GeolocationService;
use App\UserProgress\Domain\Entity\UserQuestProgress;
use App\UserProgress\Domain\Event\AbstractUserQuestProgressEvent;
use App\UserProgress\Domain\Event\QuestStepCheckEvent;
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
        private readonly PlatformResolver $platformResolver,
        private readonly QuestLikeService $questLikeService,
        private readonly QuestStepRepositoryInterface $questStepRepository,
        private readonly GeolocationService $geolocationService
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
        
        $firstStep = $this->questStepRepository->findFirstActiveByQuest($questId);
        if ($firstStep === null) {
            throw QuestStepNotFoundException::withQuestStepAndNumber($questId, 0);
        }
        $progress->setCurrentStepNumber($firstStep->getNumber());

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
        $this->storeEvents($progress);
        $this->progressRepository->delete($progress);
    }

    /**
     * @return array{data: array<array<string, mixed>>, meta: array<string, int>}
     */
    public function getUserProgress(Uuid $userId, ?string $status = null): array
    {
        $progressRecords = $this->progressRepository->findByUserIdAndStatus($userId, $status);

        // Собираем все quest IDs для batch-запроса лайков
        $questIds = array_map(fn($progress) => $progress->getQuestId(), $progressRecords);
        
        // Один запрос для всех квестов
        $likedMap = $this->questLikeService->getLikedStatusMap($userId, $questIds);

        // Build response with quest details
        $data = [];
        foreach ($progressRecords as $progress) {
            $quest = $this->questRepository->findById($progress->getQuestId());
            $questIdString = $progress->getQuestId()->toRfc4122();
            
            $progressData = [
                'questId' => $questIdString,
                'status' => $progress->getStatus()->value,
                'completedAt' => $progress->getCompletedAt()?->format('Y-m-d H:i:s'),
                'startedAt' => $progress->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $progress->getUpdatedAt()->format('Y-m-d H:i:s'),
                'isLiked' => $likedMap[$questIdString] ?? false,
            ];

            // Include quest details if quest exists
            if ($quest !== null) {
                $progressData['quest'] = $quest->toArray();
            }

            $data[] = $progressData;
        }

        // Calculate metadata
        $allProgress = $this->progressRepository->findByUserId($userId);
        $likedQuests = $this->questLikeService->getLikedQuests($userId);
        
        $meta = [
            'total' => count($allProgress),
            'completed' => count(array_filter($allProgress, fn($p) => $p->getStatus()->isCompleted())),
            'in_progress' => count(array_filter($allProgress, fn($p) => $p->getStatus()->isActive())),
            'paused' => count(array_filter($allProgress, fn($p) => $p->getStatus()->isPaused())),
            'liked' => count($likedQuests),
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
     * Check quest step geolocation and advance to next step if valid
     *
     * @return array{success: bool, nextStepNumber?: int, completed?: bool, distance: float, error?: string}
     * @throws ProgressNotFoundException if quest is not active
     */
    public function checkQuestStep(
        Uuid $userId,
        Uuid $questId,
        float $userLat,
        float $userLng
    ): array {
        // Get active progress
        $progress = $this->progressRepository->findActiveByUserId($userId);
        
        if ($progress === null) {
            // @todo Store failed check event
            throw ProgressNotFoundException::forUserAndQuest($userId, $questId);
        }

        $currentStepNumber = $progress->getCurrentStepNumber();
        if ($currentStepNumber === null) {
            // @todo Store failed check event
            throw ProgressNotFoundException::forUserAndQuest($userId, $questId);
        }

        $currentStep = $this->questStepRepository->findByQuestAndNumber($questId, $currentStepNumber);
        if ($currentStep === null || !$currentStep->isActive()) {
            // @todo Store failed check event
            throw ProgressNotFoundException::forUserAndQuest($userId, $questId);
        }

        $distance = $this->geolocationService->calculateDistance(
            $userLat,
            $userLng,
            $currentStep->getLat(),
            $currentStep->getLng()
        );

        $isCorrectCheck = $this->geolocationService->isWithinRadius(
            $userLat,
            $userLng,
            $currentStep->getLat(),
            $currentStep->getLng(),
            $currentStep->getRadius()
        );

        if (!$isCorrectCheck) {
            // @todo Store failed check event

            return [
                'success' => false,
                'distance' => $distance,
                'error' => 'You are outside the checkpoint radius',
            ];
        }

        $progress->check();

        // проверим не последний ли это шаг квеста
        $isLastStep = $this->questStepRepository->isLastActiveStep($questId, $currentStepNumber);

        if ($isLastStep) {
            $progress->complete();
            $nextStep = null;
        } else {
            $nextStep = $this->questStepRepository->findNextActiveByQuestAndNumber($questId, $currentStepNumber);
            if ($nextStep !== null) {
                $progress->complete();
            }
        }

        $this->storeEvents($progress);
        $this->progressRepository->save($progress);

        return [
            'success' => true,
            'nextStepNumber' => $nextStep?->getNumber(),
            'distance' => $distance,
        ];
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
