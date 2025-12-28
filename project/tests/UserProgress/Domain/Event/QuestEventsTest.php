<?php

declare(strict_types=1);

namespace App\Tests\UserProgress\Domain\Event;

use App\Platform\ValueObject\Platform;
use App\UserProgress\Domain\Event\QuestAbandonedEvent;
use App\UserProgress\Domain\Event\QuestCompletedEvent;
use App\UserProgress\Domain\Event\QuestPausedEvent;
use App\UserProgress\Domain\Event\QuestResumedEvent;
use App\UserProgress\Domain\Event\QuestStartedEvent;
use App\UserProgress\Domain\Event\QuestStepCheckEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

/**
 * Unit тесты для всех доменных событий UserQuestProgress.
 */
class QuestEventsTest extends TestCase
{
    private Uuid $aggregateId;
    private Uuid $userId;
    private Uuid $questId;
    private \DateTimeImmutable $occurredAt;
    private Platform $platform;

    protected function setUp(): void
    {
        $this->aggregateId = Uuid::v4();
        $this->userId = Uuid::v4();
        $this->questId = Uuid::v4();
        $this->occurredAt = new \DateTimeImmutable();
        $this->platform = Platform::web('Chrome', '120.0.0', 'macOS');
    }

    /**
     * @test
     */
    public function questStartedEventHasEmptyEventData(): void
    {
        $event = new QuestStartedEvent(
            $this->aggregateId,
            $this->userId,
            $this->questId,
            $this->occurredAt,
            $this->platform
        );

        $this->assertSame($this->aggregateId, $event->getAggregateId());
        $this->assertSame($this->userId, $event->getUserId());
        $this->assertSame($this->questId, $event->getQuestId());
        $this->assertSame($this->occurredAt, $event->getOccurredAt());
        $this->assertEquals($this->platform->toArray(), $event->getPlatform()->toArray());
        $this->assertSame(QuestStartedEvent::class, $event->getEventType());
        $this->assertEmpty($event->getEventData());
    }

    /**
     * @test
     */
    public function questResumedEventHasEmptyEventData(): void
    {
        $event = new QuestResumedEvent(
            $this->aggregateId,
            $this->userId,
            $this->questId,
            $this->occurredAt,
            $this->platform
        );

        $this->assertSame($this->aggregateId, $event->getAggregateId());
        $this->assertSame($this->userId, $event->getUserId());
        $this->assertSame($this->questId, $event->getQuestId());
        $this->assertSame($this->occurredAt, $event->getOccurredAt());
        $this->assertSame($this->platform, $event->getPlatform());
        $this->assertSame(QuestResumedEvent::class, $event->getEventType());
        $this->assertEmpty($event->getEventData());
    }

    /**
     * @test
     */
    public function questPausedEventHasEmptyEventData(): void
    {
        $event = new QuestPausedEvent(
            $this->aggregateId,
            $this->userId,
            $this->questId,
            $this->occurredAt,
            $this->platform
        );

        $this->assertSame($this->aggregateId, $event->getAggregateId());
        $this->assertSame($this->userId, $event->getUserId());
        $this->assertSame($this->questId, $event->getQuestId());
        $this->assertSame($this->occurredAt, $event->getOccurredAt());
        $this->assertSame($this->platform, $event->getPlatform());
        $this->assertSame(QuestPausedEvent::class, $event->getEventType());
        $this->assertEmpty($event->getEventData());
    }

    /**
     * @test
     */
    public function questCompletedEventHasEmptyEventData(): void
    {
        $event = new QuestCompletedEvent(
            $this->aggregateId,
            $this->userId,
            $this->questId,
            $this->occurredAt,
            $this->platform
        );

        $this->assertSame($this->aggregateId, $event->getAggregateId());
        $this->assertSame($this->userId, $event->getUserId());
        $this->assertSame($this->questId, $event->getQuestId());
        $this->assertSame($this->occurredAt, $event->getOccurredAt());
        $this->assertSame($this->platform, $event->getPlatform());
        $this->assertSame(QuestCompletedEvent::class, $event->getEventType());
        $this->assertEmpty($event->getEventData());
    }

    /**
     * @test
     */
    public function questAbandonedEventHasEmptyEventData(): void
    {
        $event = new QuestAbandonedEvent(
            $this->aggregateId,
            $this->userId,
            $this->questId,
            $this->occurredAt,
            $this->platform
        );

        $this->assertSame($this->aggregateId, $event->getAggregateId());
        $this->assertSame($this->userId, $event->getUserId());
        $this->assertSame($this->questId, $event->getQuestId());
        $this->assertSame($this->occurredAt, $event->getOccurredAt());
        $this->assertSame($this->platform, $event->getPlatform());
        $this->assertSame(QuestAbandonedEvent::class, $event->getEventType());
        $this->assertEmpty($event->getEventData());
    }

    /**
     * @test
     */
    public function questStepCheckEventContainsLocationData(): void
    {
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

        $this->assertSame($this->aggregateId, $event->getAggregateId());
        $this->assertSame($this->userId, $event->getUserId());
        $this->assertSame($this->questId, $event->getQuestId());
        $this->assertSame($this->occurredAt, $event->getOccurredAt());
        $this->assertSame($this->platform, $event->getPlatform());
        $this->assertSame(QuestStepCheckEvent::class, $event->getEventType());

        // Проверка eventData
        $eventData = $event->getEventData();
        $this->assertArrayHasKey('client_latitude', $eventData);
        $this->assertArrayHasKey('client_longitude', $eventData);
        $this->assertArrayHasKey('distance_to_point', $eventData);
        $this->assertArrayHasKey('check_passed', $eventData);

        $this->assertSame($clientLat, $eventData['client_latitude']);
        $this->assertSame($clientLon, $eventData['client_longitude']);
        $this->assertSame($distance, $eventData['distance_to_point']);
        $this->assertSame($checkPassed, $eventData['check_passed']);

        // Проверка getter методов
        $this->assertSame($clientLat, $event->getClientLatitude());
        $this->assertSame($clientLon, $event->getClientLongitude());
        $this->assertSame($distance, $event->getDistanceToPoint());
        $this->assertTrue($event->isCheckPassed());
    }

    /**
     * @test
     */
    public function questStepCheckEventHandlesFailedCheck(): void
    {
        $event = new QuestStepCheckEvent(
            $this->aggregateId,
            $this->userId,
            $this->questId,
            $this->occurredAt,
            $this->platform,
            55.7558,
            37.6173,
            150.0, // Далеко от точки
            false  // Проверка не пройдена
        );

        $eventData = $event->getEventData();
        $this->assertFalse($eventData['check_passed']);
        $this->assertFalse($event->isCheckPassed());
    }

    /**
     * @test
     */
    public function eventsPlatformDataIsPreserved(): void
    {
        $mobilePlatform = Platform::ios('1.2.3', '17.2', 'iPhone 14');

        $event = new QuestStartedEvent(
            $this->aggregateId,
            $this->userId,
            $this->questId,
            $this->occurredAt,
            $mobilePlatform
        );

        $platform = $event->getPlatform();
        $this->assertSame('ios', $platform->getType());
        $this->assertTrue($platform->isMobile());
        $platformArray = $platform->toArray();
        $this->assertSame('1.2.3', $platformArray['app_version']);
        $this->assertSame('17.2', $platformArray['os_version']);
        $this->assertSame('iPhone 14', $platformArray['device_model']);
    }
}

