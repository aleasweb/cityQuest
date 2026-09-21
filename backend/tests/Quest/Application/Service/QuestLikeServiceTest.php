<?php

declare(strict_types=1);

namespace App\Tests\Quest\Application\Service;

use App\Quest\Application\Service\QuestLikeService;
use App\Quest\Domain\Entity\Quest;
use App\Quest\Domain\Entity\QuestLike;
use App\Quest\Domain\Exception\QuestNotFoundException;
use App\Quest\Domain\Repository\QuestLikeRepositoryInterface;
use App\Quest\Domain\Repository\QuestRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class QuestLikeServiceTest extends TestCase
{
    private QuestLikeRepositoryInterface $likeRepository;
    private QuestRepositoryInterface $questRepository;
    private QuestLikeService $service;

    protected function setUp(): void
    {
        $this->likeRepository = $this->createMock(QuestLikeRepositoryInterface::class);
        $this->questRepository = $this->createMock(QuestRepositoryInterface::class);

        $this->service = new QuestLikeService(
            $this->likeRepository,
            $this->questRepository
        );
    }

    public function testToggleLikeAddsLikeWhenNotLiked(): void
    {
        $userId = Uuid::v4();
        $questId = Uuid::v4();
        $quest = new Quest('Test Quest');

        $this->questRepository
            ->expects($this->once())
            ->method('findById')
            ->with($questId)
            ->willReturn($quest);

        $this->likeRepository
            ->expects($this->once())
            ->method('findByUserAndQuest')
            ->with($userId, $questId)
            ->willReturn(null);

        $this->likeRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(QuestLike::class));

        $this->likeRepository
            ->expects($this->once())
            ->method('countByQuest')
            ->with($questId)
            ->willReturn(1);

        $result = $this->service->toggleLike($userId, $questId);

        $this->assertTrue($result['liked']);
        $this->assertEquals(1, $result['likesCount']);
    }

    public function testToggleLikeRemovesLikeWhenAlreadyLiked(): void
    {
        $userId = Uuid::v4();
        $questId = Uuid::v4();
        $quest = new Quest('Test Quest');
        $existingLike = new QuestLike($userId, $questId);

        $this->questRepository
            ->expects($this->once())
            ->method('findById')
            ->with($questId)
            ->willReturn($quest);

        $this->likeRepository
            ->expects($this->once())
            ->method('findByUserAndQuest')
            ->with($userId, $questId)
            ->willReturn($existingLike);

        $this->likeRepository
            ->expects($this->once())
            ->method('remove')
            ->with($existingLike);

        $this->likeRepository
            ->expects($this->once())
            ->method('countByQuest')
            ->with($questId)
            ->willReturn(0);

        $result = $this->service->toggleLike($userId, $questId);

        $this->assertFalse($result['liked']);
        $this->assertEquals(0, $result['likesCount']);
    }

    public function testToggleLikeThrowsExceptionWhenQuestNotFound(): void
    {
        $userId = Uuid::v4();
        $questId = Uuid::v4();

        $this->questRepository
            ->expects($this->once())
            ->method('findById')
            ->with($questId)
            ->willReturn(null);

        $this->expectException(QuestNotFoundException::class);

        $this->service->toggleLike($userId, $questId);
    }

    public function testIsLikedReturnsTrueWhenLiked(): void
    {
        $userId = Uuid::v4();
        $questId = Uuid::v4();
        $like = new QuestLike($userId, $questId);

        $this->likeRepository
            ->expects($this->once())
            ->method('findByUserAndQuest')
            ->with($userId, $questId)
            ->willReturn($like);

        $result = $this->service->isLiked($userId, $questId);

        $this->assertTrue($result);
    }

    public function testIsLikedReturnsFalseWhenNotLiked(): void
    {
        $userId = Uuid::v4();
        $questId = Uuid::v4();

        $this->likeRepository
            ->expects($this->once())
            ->method('findByUserAndQuest')
            ->with($userId, $questId)
            ->willReturn(null);

        $result = $this->service->isLiked($userId, $questId);

        $this->assertFalse($result);
    }

    public function testGetLikedQuestsReturnsUserLikes(): void
    {
        $userId = Uuid::v4();
        $questId1 = Uuid::v4();
        $questId2 = Uuid::v4();

        $likes = [
            new QuestLike($userId, $questId1),
            new QuestLike($userId, $questId2),
        ];

        $this->likeRepository
            ->expects($this->once())
            ->method('findByUser')
            ->with($userId)
            ->willReturn($likes);

        $result = $this->service->getLikedQuests($userId);

        $this->assertCount(2, $result);
        $this->assertSame($likes, $result);
    }
}

