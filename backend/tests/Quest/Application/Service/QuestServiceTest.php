<?php

declare(strict_types=1);

namespace App\Tests\Quest\Application\Service;

use App\Quest\Application\Service\QuestService;
use App\Quest\Domain\Entity\Quest;
use App\Quest\Domain\Exception\QuestNotFoundException;
use App\Quest\Domain\Repository\QuestRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class QuestServiceTest extends TestCase
{
    private QuestRepositoryInterface $questRepository;
    private QuestService $questService;

    protected function setUp(): void
    {
        $this->questRepository = $this->createMock(QuestRepositoryInterface::class);
        $this->questService = new QuestService($this->questRepository);
    }

    public function testGetQuestByIdReturnsQuestData(): void
    {
        // Arrange
        $questId = Uuid::v4();
        $quest = new Quest('Test Quest');
        $quest->setDescription('Test description');
        $quest->setCity('Moscow');
        $quest->setDifficulty('medium');
        $quest->setDurationMinutes(120);
        $quest->setDistanceKm(5.5);
        $quest->setAuthor('Test Author');
        $quest->setLikesCount(42);
        $quest->setIsPopular(true);

        $this->questRepository
            ->expects($this->once())
            ->method('findById')
            ->with($questId)
            ->willReturn($quest);

        // Act
        $result = $this->questService->getQuestById($questId);

        // Assert
        $this->assertIsArray($result);
        $this->assertEquals((string) $quest->getId(), $result['id']);
        $this->assertEquals('Test Quest', $result['title']);
        $this->assertEquals('Test description', $result['description']);
        $this->assertEquals('Moscow', $result['city']);
        $this->assertEquals('medium', $result['difficulty']);
        $this->assertEquals(120, $result['durationMinutes']);
        $this->assertEquals(5.5, $result['distanceKm']);
        $this->assertEquals('Test Author', $result['author']);
        $this->assertEquals(42, $result['likesCount']);
        $this->assertTrue($result['isPopular']);
        $this->assertArrayHasKey('createdAt', $result);
        $this->assertArrayHasKey('updatedAt', $result);
    }

    public function testGetQuestByIdThrowsExceptionForNonExistentQuest(): void
    {
        // Arrange
        $questId = Uuid::v4();

        $this->questRepository
            ->expects($this->once())
            ->method('findById')
            ->with($questId)
            ->willReturn(null);

        // Assert
        $this->expectException(QuestNotFoundException::class);
        $this->expectExceptionMessage(sprintf('Quest with id "%s" not found', (string) $questId));

        // Act
        $this->questService->getQuestById($questId);
    }
}
