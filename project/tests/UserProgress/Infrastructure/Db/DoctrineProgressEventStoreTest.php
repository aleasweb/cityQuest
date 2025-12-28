<?php

declare(strict_types=1);

namespace App\Tests\UserProgress\Infrastructure\Db;

use App\Platform\ValueObject\Platform;
use App\Tests\Helper\DatabaseTestTrait;
use App\UserProgress\Domain\Event\QuestAbandonedEvent;
use App\UserProgress\Domain\Event\QuestCompletedEvent;
use App\UserProgress\Domain\Event\QuestPausedEvent;
use App\UserProgress\Domain\Event\QuestResumedEvent;
use App\UserProgress\Domain\Event\QuestStartedEvent;
use App\UserProgress\Domain\Event\QuestStepCheckEvent;
use App\UserProgress\Domain\Repository\ProgressEventStoreInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Uuid;

/**
 * Integration тесты для DoctrineProgressEventStore.
 * 
 * Тестирует сохранение и извлечение доменных событий из БД.
 */
class DoctrineProgressEventStoreTest extends KernelTestCase
{
    use DatabaseTestTrait;

    private ProgressEventStoreInterface $eventStore;
    private Uuid $aggregateId;
    private Uuid $userId;
    private Uuid $questId;
    private \DateTimeImmutable $occurredAt;
    private Platform $platform;

    protected function setUp(): void
    {
        self::bootKernel();
        
        $this->eventStore = self::getContainer()->get(ProgressEventStoreInterface::class);
        $this->clearTables(['domain_events_progress']);

        $this->aggregateId = Uuid::v4();
        $this->userId = Uuid::v4();
        $this->questId = Uuid::v4();
        $this->occurredAt = new \DateTimeImmutable();
        $this->platform = Platform::web('Chrome', '120.0.0', 'macOS');
    }

    protected function tearDown(): void
    {
        $this->closeEntityManager();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function storeAndRetrieveQuestStartedEvent(): void
    {
        // Arrange
        $event = new QuestStartedEvent(
            $this->aggregateId,
            $this->userId,
            $this->questId,
            $this->occurredAt,
            $this->platform
        );

        // Act
        $this->eventStore->store($event);
        $events = $this->eventStore->getByAggregateId($this->aggregateId);

        // Assert
        $this->assertCount(1, $events);
        $retrievedEvent = $events[0];
        
        $this->assertInstanceOf(QuestStartedEvent::class, $retrievedEvent);
        $this->assertEquals($this->aggregateId, $retrievedEvent->getAggregateId());
        $this->assertEquals($this->userId, $retrievedEvent->getUserId());
        $this->assertEquals($this->questId, $retrievedEvent->getQuestId());
        $this->assertEquals($this->platform, $retrievedEvent->getPlatform());
        $this->assertEmpty($retrievedEvent->getEventData());
    }

    /**
     * @test
     */
    public function storeAndRetrieveQuestStepCheckEvent(): void
    {
        // Arrange
        $clientLat = 55.7558;
        $clientLon = 37.6173;
        $distance = 15.5;
        $checkPassed = true;

        $event = new QuestStepCheckEvent(
            $this->aggregateId,
            $this->userId,
            $this->questId,
            $this->occurredAt,
            $this->platform,
            $clientLat,
            $clientLon,
            $distance,
            $checkPassed
        );

        // Act
        $this->eventStore->store($event);
        $events = $this->eventStore->getByAggregateId($this->aggregateId);

        // Assert
        $this->assertCount(1, $events);
        $retrievedEvent = $events[0];
        
        $this->assertInstanceOf(QuestStepCheckEvent::class, $retrievedEvent);
        $this->assertEquals($clientLat, $retrievedEvent->getClientLatitude());
        $this->assertEquals($clientLon, $retrievedEvent->getClientLongitude());
        $this->assertEquals($distance, $retrievedEvent->getDistanceToPoint());
        $this->assertTrue($retrievedEvent->isCheckPassed());
    }

    /**
     * @test
     */
    public function storeMultipleEventsAndRetrieveByAggregateId(): void
    {
        // Arrange
        $event1 = new QuestStartedEvent(
            $this->aggregateId,
            $this->userId,
            $this->questId,
            $this->occurredAt,
            $this->platform
        );

        $event2 = new QuestPausedEvent(
            $this->aggregateId,
            $this->userId,
            $this->questId,
            $this->occurredAt->modify('+1 hour'),
            $this->platform
        );

        $event3 = new QuestResumedEvent(
            $this->aggregateId,
            $this->userId,
            $this->questId,
            $this->occurredAt->modify('+2 hours'),
            $this->platform
        );

        // Act
        $this->eventStore->store($event1);
        $this->eventStore->store($event2);
        $this->eventStore->store($event3);
        
        $events = $this->eventStore->getByAggregateId($this->aggregateId);

        // Assert
        $this->assertCount(3, $events);
        $this->assertInstanceOf(QuestStartedEvent::class, $events[0]);
        $this->assertInstanceOf(QuestPausedEvent::class, $events[1]);
        $this->assertInstanceOf(QuestResumedEvent::class, $events[2]);
    }

    /**
     * @test
     */
    public function getByUserIdReturnsOnlyUserEvents(): void
    {
        // Arrange
        $otherUserId = Uuid::v4();
        
        $userEvent = new QuestStartedEvent(
            $this->aggregateId,
            $this->userId,
            $this->questId,
            $this->occurredAt,
            $this->platform
        );

        $otherUserEvent = new QuestStartedEvent(
            Uuid::v4(),
            $otherUserId,
            $this->questId,
            $this->occurredAt,
            $this->platform
        );

        // Act
        $this->eventStore->store($userEvent);
        $this->eventStore->store($otherUserEvent);
        
        $events = $this->eventStore->getByUserId($this->userId);

        // Assert
        $this->assertCount(1, $events);
        $this->assertEquals($this->userId, $events[0]->getUserId());
    }

    /**
     * @test
     */
    public function getByQuestIdReturnsOnlyQuestEvents(): void
    {
        // Arrange
        $otherQuestId = Uuid::v4();
        
        $questEvent = new QuestStartedEvent(
            $this->aggregateId,
            $this->userId,
            $this->questId,
            $this->occurredAt,
            $this->platform
        );

        $otherQuestEvent = new QuestStartedEvent(
            Uuid::v4(),
            $this->userId,
            $otherQuestId,
            $this->occurredAt,
            $this->platform
        );

        // Act
        $this->eventStore->store($questEvent);
        $this->eventStore->store($otherQuestEvent);
        
        $events = $this->eventStore->getByQuestId($this->questId);

        // Assert
        $this->assertCount(1, $events);
        $this->assertEquals($this->questId, $events[0]->getQuestId());
    }

    /**
     * @test
     */
    public function storeAllEventTypes(): void
    {
        // Arrange
        $events = [
            new QuestStartedEvent($this->aggregateId, $this->userId, $this->questId, $this->occurredAt, $this->platform),
            new QuestResumedEvent($this->aggregateId, $this->userId, $this->questId, $this->occurredAt, $this->platform),
            new QuestPausedEvent($this->aggregateId, $this->userId, $this->questId, $this->occurredAt, $this->platform),
            new QuestCompletedEvent($this->aggregateId, $this->userId, $this->questId, $this->occurredAt, $this->platform),
            new QuestAbandonedEvent($this->aggregateId, $this->userId, $this->questId, $this->occurredAt, $this->platform),
            new QuestStepCheckEvent($this->aggregateId, $this->userId, $this->questId, $this->occurredAt, $this->platform, 55.0, 37.0, 10.0, true),
        ];

        // Act
        foreach ($events as $event) {
            $this->eventStore->store($event);
        }
        
        $retrievedEvents = $this->eventStore->getByAggregateId($this->aggregateId);

        // Assert
        $this->assertCount(6, $retrievedEvents);
        $this->assertInstanceOf(QuestStartedEvent::class, $retrievedEvents[0]);
        $this->assertInstanceOf(QuestResumedEvent::class, $retrievedEvents[1]);
        $this->assertInstanceOf(QuestPausedEvent::class, $retrievedEvents[2]);
        $this->assertInstanceOf(QuestCompletedEvent::class, $retrievedEvents[3]);
        $this->assertInstanceOf(QuestAbandonedEvent::class, $retrievedEvents[4]);
        $this->assertInstanceOf(QuestStepCheckEvent::class, $retrievedEvents[5]);
    }

    /**
     * @test
     */
    public function eventsAreOrderedByRecordedAtAsc(): void
    {
        // Arrange
        $time1 = new \DateTimeImmutable('2024-01-01 10:00:00');
        $time2 = new \DateTimeImmutable('2024-01-01 11:00:00');
        $time3 = new \DateTimeImmutable('2024-01-01 12:00:00');

        // Сохраняем в обратном порядке
        $this->eventStore->store(new QuestPausedEvent($this->aggregateId, $this->userId, $this->questId, $time3, $this->platform));
        $this->eventStore->store(new QuestStartedEvent($this->aggregateId, $this->userId, $this->questId, $time1, $this->platform));
        $this->eventStore->store(new QuestResumedEvent($this->aggregateId, $this->userId, $this->questId, $time2, $this->platform));

        // Act
        $events = $this->eventStore->getByAggregateId($this->aggregateId);

        // Assert
        $this->assertCount(3, $events);
        $this->assertInstanceOf(QuestStartedEvent::class, $events[0]);
        $this->assertInstanceOf(QuestResumedEvent::class, $events[1]);
        $this->assertInstanceOf(QuestPausedEvent::class, $events[2]);
    }
}

