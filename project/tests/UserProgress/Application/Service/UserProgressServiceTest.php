<?php

declare(strict_types=1);

namespace App\Tests\UserProgress\Application\Service;

use App\Quest\Domain\Entity\Quest;
use App\Quest\Domain\Exception\QuestNotFoundException;
use App\Quest\Domain\Repository\QuestRepositoryInterface;
use App\UserProgress\Application\Service\UserProgressService;
use App\UserProgress\Domain\Entity\UserQuestProgress;
use App\UserProgress\Domain\Exception\ActiveQuestExistsException;
use App\UserProgress\Domain\Exception\InvalidQuestStatusException;
use App\UserProgress\Domain\Exception\ProgressNotFoundException;
use App\UserProgress\Domain\Repository\UserQuestProgressRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class UserProgressServiceTest extends TestCase
{
    private UserQuestProgressRepositoryInterface $progressRepository;
    private QuestRepositoryInterface $questRepository;
    private UserProgressService $service;

    protected function setUp(): void
    {
        $this->progressRepository = $this->createMock(UserQuestProgressRepositoryInterface::class);
        $this->questRepository = $this->createMock(QuestRepositoryInterface::class);
        $this->service = new UserProgressService($this->progressRepository, $this->questRepository);
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
        $this->assertTrue($result->isActive());
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
        $pausedProgress->pause();

        $this->questRepository->method('findById')->willReturn($quest);
        $this->progressRepository->method('findActiveByUserId')->willReturn(null);
        $this->progressRepository->method('findByUserIdAndQuestId')->willReturn($pausedProgress);
        
        $this->progressRepository->expects($this->once())->method('save');

        $result = $this->service->startQuest($userId, $questId);

        $this->assertTrue($result->isActive());
    }

    public function testPauseQuestChangesStatusToPaused(): void
    {
        $userId = Uuid::v4();
        $questId = Uuid::v4();
        $activeProgress = new UserQuestProgress($userId, $questId);

        $this->progressRepository->method('findByUserIdAndQuestId')->willReturn($activeProgress);
        $this->progressRepository->expects($this->once())->method('save');

        $result = $this->service->pauseQuest($userId, $questId);

        $this->assertTrue($result->isPaused());
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

        $this->progressRepository->method('findByUserIdAndQuestId')->willReturn($activeProgress);
        $this->progressRepository->expects($this->once())->method('save');

        $result = $this->service->completeQuest($userId, $questId);

        $this->assertTrue($result->isCompleted());
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
        $quest = new Quest('Test Quest');

        $this->progressRepository->method('findByUserIdWithFilters')->willReturn([$progress]);
        $this->progressRepository->method('findByUserId')->willReturn([$progress]);
        $this->questRepository->method('findById')->willReturn($quest);

        $result = $this->service->getUserProgress($userId);

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
        $this->assertCount(1, $result['data']);
        $this->assertEquals(1, $result['meta']['total']);
        $this->assertEquals(1, $result['meta']['in_progress']);
    }
}
