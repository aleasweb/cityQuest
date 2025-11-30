<?php

declare(strict_types=1);

namespace App\Tests\UserProgress\Application\Service;

use App\Quest\Domain\Entity\Quest;
use App\Quest\Domain\Exception\QuestNotFoundException;
use App\Quest\Domain\Repository\QuestRepositoryInterface;
use App\UserProgress\Application\Service\QuestLikeService;
use App\UserProgress\Domain\Entity\UserQuestProgress;
use App\UserProgress\Domain\Repository\UserQuestProgressRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class QuestLikeServiceTest extends TestCase
{
    private UserQuestProgressRepositoryInterface $progressRepository;
    private QuestRepositoryInterface $questRepository;
    private EntityManagerInterface $entityManager;
    private QuestLikeService $service;

    protected function setUp(): void
    {
        $this->progressRepository = $this->createMock(UserQuestProgressRepositoryInterface::class);
        $this->questRepository = $this->createMock(QuestRepositoryInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        
        $this->service = new QuestLikeService(
            $this->progressRepository,
            $this->questRepository,
            $this->entityManager
        );
    }

    public function testToggleLikeCreatesProgressWhenNotExists(): void
    {
        $userId = Uuid::v4();
        $questId = Uuid::v4();
        $quest = new Quest('Test Quest');

        $this->questRepository->method('findById')->willReturn($quest);
        $this->progressRepository->method('findByUserIdAndQuestId')->willReturn(null);
        
        $this->entityManager->expects($this->once())->method('beginTransaction');
        $this->entityManager->expects($this->once())->method('commit');
        $this->progressRepository->expects($this->once())->method('save');
        $this->questRepository->expects($this->once())->method('incrementLikesCount');

        $result = $this->service->toggleLike($userId, $questId);

        $this->assertTrue($result['liked']);
    }

    public function testToggleLikeTogglesExistingLike(): void
    {
        $userId = Uuid::v4();
        $questId = Uuid::v4();
        $quest = new Quest('Test Quest');
        $progress = new UserQuestProgress($userId, $questId);
        $progress->like(); // Initially liked

        $this->questRepository->method('findById')->willReturn($quest);
        $this->progressRepository->method('findByUserIdAndQuestId')->willReturn($progress);
        
        $this->entityManager->expects($this->once())->method('beginTransaction');
        $this->entityManager->expects($this->once())->method('commit');
        $this->progressRepository->expects($this->once())->method('save');
        $this->questRepository->expects($this->once())->method('decrementLikesCount');

        $result = $this->service->toggleLike($userId, $questId);

        $this->assertFalse($result['liked']);
    }

    public function testToggleLikeThrowsExceptionWhenQuestNotFound(): void
    {
        $userId = Uuid::v4();
        $questId = Uuid::v4();

        $this->questRepository->method('findById')->willReturn(null);

        $this->expectException(QuestNotFoundException::class);

        $this->service->toggleLike($userId, $questId);
    }

    public function testIsLikedReturnsTrueWhenLiked(): void
    {
        $userId = Uuid::v4();
        $questId = Uuid::v4();
        $progress = new UserQuestProgress($userId, $questId);
        $progress->like();

        $this->progressRepository->method('findByUserIdAndQuestId')->willReturn($progress);

        $result = $this->service->isLiked($userId, $questId);

        $this->assertTrue($result);
    }

    public function testIsLikedReturnsFalseWhenNotLiked(): void
    {
        $userId = Uuid::v4();
        $questId = Uuid::v4();

        $this->progressRepository->method('findByUserIdAndQuestId')->willReturn(null);

        $result = $this->service->isLiked($userId, $questId);

        $this->assertFalse($result);
    }
}
