<?php

declare(strict_types=1);

namespace App\Tests\UserProgress\Application\Service;

use App\Platform\Application\Service\PlatformResolver;
use App\Platform\ValueObject\Platform;
use App\Quest\Application\Service\QuestLikeService;
use App\Quest\Domain\Entity\Quest;
use App\Quest\Domain\Exception\QuestNotFoundException;
use App\Quest\Domain\Repository\QuestLikeRepositoryInterface;
use App\Quest\Domain\Repository\QuestRepositoryInterface;
use App\UserProgress\Application\Service\UserProgressService;
use App\UserProgress\Domain\Entity\UserQuestProgress;
use App\UserProgress\Domain\Exception\ActiveQuestExistsException;
use App\UserProgress\Domain\Exception\ProgressNotFoundException;
use App\UserProgress\Domain\Repository\ProgressEventStoreInterface;
use App\UserProgress\Domain\Repository\UserQuestProgressRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class UserProgressServiceTest extends TestCase
{
    private UserQuestProgressRepositoryInterface $progressRepository;
    private QuestRepositoryInterface $questRepository;
    private ProgressEventStoreInterface $eventStore;
    private PlatformResolver $platformResolver;
    private QuestLikeService $questLikeService;
    private QuestLikeRepositoryInterface $likeRepository;
    private UserProgressService $service;

    protected function setUp(): void
    {
        $this->progressRepository = $this->createMock(UserQuestProgressRepositoryInterface::class);
        $this->questRepository = $this->createMock(QuestRepositoryInterface::class);
        $this->eventStore = $this->createMock(ProgressEventStoreInterface::class);
        $this->platformResolver = $this->createMock(PlatformResolver::class);
        
        // Create QuestLikeService with mocked dependencies (final class workaround)
        $this->likeRepository = $this->createMock(QuestLikeRepositoryInterface::class);
        $this->questLikeService = new QuestLikeService($this->likeRepository, $this->questRepository);
        
        $platform = Platform::web('Chrome', '120.0.0', 'macOS');
        $this->platformResolver->method('resolve')->willReturn($platform);
        
        $this->service = new UserProgressService(
            $this->progressRepository,
            $this->questRepository,
            $this->eventStore,
            $this->platformResolver,
            $this->questLikeService
        );
    }

    public function testStartQuestCreatesNewProgress(): void
    {
        $userId = Uuid::v4();
        $questId = Uuid::v4();
        $quest = new Quest('Test Quest');

        $this->questRepository->method('findById')->willReturn($quest);
        $this->progressRepository->method('findActiveByUserId')->willReturn(null);
        $this->progressRepository->method('findByUserIdAndQuestId')->willReturn(null);
        
        $this->progressRepository->expects($this->once())->method('save');

        $result = $this->service->startQuest($userId, $questId);

        $this->assertInstanceOf(UserQuestProgress::class, $result);
        $this->assertTrue($result->getStatus()->isActive());
    }

    public function testStartQuestThrowsExceptionWhenQuestNotFound(): void
    {
        $userId = Uuid::v4();
        $questId = Uuid::v4();

        $this->questRepository->method('findById')->willReturn(null);

        $this->expectException(QuestNotFoundException::class);

        $this->service->startQuest($userId, $questId);
    }

    public function testStartQuestThrowsExceptionWhenActiveQuestExists(): void
    {
        $userId = Uuid::v4();
        $questId = Uuid::v4();
        $activeQuestId = Uuid::v4();
        $quest = new Quest('Test Quest');
        $activeProgress = new UserQuestProgress($userId, $activeQuestId);

        $this->questRepository->method('findById')->willReturn($quest);
        $this->progressRepository->method('findActiveByUserId')->willReturn($activeProgress);

        $this->expectException(ActiveQuestExistsException::class);

        $this->service->startQuest($userId, $questId);
    }

    public function testStartQuestResumesFromPaused(): void
    {
        $userId = Uuid::v4();
        $questId = Uuid::v4();
        $quest = new Quest('Test Quest');
        $pausedProgress = new UserQuestProgress($userId, $questId);
        $pausedProgress->start();
        $pausedProgress->pause();

        $this->questRepository->method('findById')->willReturn($quest);
        $this->progressRepository->method('findActiveByUserId')->willReturn(null);
        $this->progressRepository->method('findByUserIdAndQuestId')->willReturn($pausedProgress);
        
        $this->progressRepository->expects($this->once())->method('save');

        $result = $this->service->startQuest($userId, $questId);

        $this->assertTrue($result->getStatus()->isActive());
    }

    public function testPauseQuestChangesStatusToPaused(): void
    {
        $userId = Uuid::v4();
        $questId = Uuid::v4();
        $activeProgress = new UserQuestProgress($userId, $questId);
        $activeProgress->start();

        $this->progressRepository->method('findByUserIdAndQuestId')->willReturn($activeProgress);
        $this->progressRepository->expects($this->once())->method('save');

        $result = $this->service->pauseQuest($userId, $questId);

        $this->assertTrue($result->getStatus()->isPaused());
    }

    public function testPauseQuestThrowsExceptionWhenProgressNotFound(): void
    {
        $userId = Uuid::v4();
        $questId = Uuid::v4();

        $this->progressRepository->method('findByUserIdAndQuestId')->willReturn(null);

        $this->expectException(ProgressNotFoundException::class);

        $this->service->pauseQuest($userId, $questId);
    }

    public function testCompleteQuestChangesStatusToCompleted(): void
    {
        $userId = Uuid::v4();
        $questId = Uuid::v4();
        $activeProgress = new UserQuestProgress($userId, $questId);
        $activeProgress->start();

        $this->progressRepository->method('findByUserIdAndQuestId')->willReturn($activeProgress);
        $this->progressRepository->expects($this->once())->method('save');

        $result = $this->service->completeQuest($userId, $questId);

        $this->assertTrue($result->getStatus()->isCompleted());
        $this->assertNotNull($result->getCompletedAt());
    }

    public function testCompleteQuestThrowsExceptionWhenProgressNotFound(): void
    {
        $userId = Uuid::v4();
        $questId = Uuid::v4();

        $this->progressRepository->method('findByUserIdAndQuestId')->willReturn(null);

        $this->expectException(ProgressNotFoundException::class);

        $this->service->completeQuest($userId, $questId);
    }

    public function testGetUserProgressReturnsFormattedData(): void
    {
        $userId = Uuid::v4();
        $questId = Uuid::v4();
        $progress = new UserQuestProgress($userId, $questId);
        $progress->start();
        $quest = new Quest('Test Quest');

        $this->progressRepository->method('findByUserIdAndStatus')->willReturn([$progress]);
        $this->progressRepository->method('findByUserId')->willReturn([$progress]);
        $this->questRepository->method('findById')->willReturn($quest);
        
        // Mock batch method getLikedStatusMap (оптимизация N+1)
        $this->likeRepository->method('findLikedQuestIds')
            ->with($userId, [$questId])
            ->willReturn([]);
        
        // Mock getLikedQuests for meta.liked count
        $this->likeRepository->method('findByUser')
            ->with($userId)
            ->willReturn([]);

        $result = $this->service->getUserProgress($userId);

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
        $this->assertCount(1, $result['data']);
        $this->assertEquals(1, $result['meta']['total']);
        $this->assertEquals(1, $result['meta']['in_progress']);
        $this->assertEquals(0, $result['meta']['liked']);
        $this->assertArrayHasKey('isLiked', $result['data'][0]);
        $this->assertFalse($result['data'][0]['isLiked']);
    }
}
