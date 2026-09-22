<?php

declare(strict_types=1);

namespace App\Tests\Shared\Domain\Event;

use App\Platform\ValueObject\Platform;
use App\Shared\Domain\Event\DomainEventInterface;
use App\Shared\Domain\Event\RecordsEvents;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

/**
 * Unit тесты для RecordsEvents trait.
 * 
 * Тестирует механизм накопления и извлечения доменных событий.
 */
class RecordsEventsTest extends TestCase
{
    /**
     * Тестовый агрегат для использования RecordsEvents trait.
     */
    private TestAggregate $aggregate;

    protected function setUp(): void
    {
        $this->aggregate = new TestAggregate();
    }

    /**
     * @test
     */
    public function recordEventAddsEventToCollection(): void
    {
        // Arrange
        $event = $this->createMockEvent();

        // Act
        $this->aggregate->triggerEvent($event);
        $events = $this->aggregate->pull();

        // Assert
        $this->assertCount(1, $events);
        $this->assertSame($event, $events[0]);
    }

    /**
     * @test
     */
    public function pullDomainEventsReturnsEventsAndClearsCollection(): void
    {
        // Arrange
        $event1 = $this->createMockEvent();
        $event2 = $this->createMockEvent();

        $this->aggregate->triggerEvent($event1);
        $this->aggregate->triggerEvent($event2);

        // Act
        $firstPull = $this->aggregate->pull();
        $secondPull = $this->aggregate->pull();

        // Assert
        $this->assertCount(2, $firstPull, 'Первый pull должен вернуть 2 события');
        $this->assertCount(0, $secondPull, 'Второй pull должен вернуть пустой массив');
    }

    /**
     * @test
     */
    public function pullDomainEventsReturnsEmptyArrayWhenNoEvents(): void
    {
        // Act
        $events = $this->aggregate->pull();

        // Assert
        $this->assertIsArray($events);
        $this->assertEmpty($events);
    }

    /**
     * @test
     */
    public function multipleEventsCanBeRecorded(): void
    {
        // Arrange
        $event1 = $this->createMockEvent();
        $event2 = $this->createMockEvent();
        $event3 = $this->createMockEvent();

        // Act
        $this->aggregate->triggerEvent($event1);
        $this->aggregate->triggerEvent($event2);
        $this->aggregate->triggerEvent($event3);
        
        $events = $this->aggregate->pull();

        // Assert
        $this->assertCount(3, $events);
        $this->assertSame($event1, $events[0]);
        $this->assertSame($event2, $events[1]);
        $this->assertSame($event3, $events[2]);
    }

    /**
     * Создать mock объект DomainEventInterface.
     */
    private function createMockEvent(): DomainEventInterface
    {
        $event = $this->createMock(DomainEventInterface::class);
        
        $event->method('getAggregateId')
            ->willReturn(Uuid::v4());
        
        $event->method('getOccurredAt')
            ->willReturn(new \DateTimeImmutable());
        
        $event->method('getEventType')
            ->willReturn('MockEvent');
        
        $event->method('getPlatform')
            ->willReturn(Platform::web('Chrome', '120.0.0', 'macOS'));
        
        $event->method('getEventData')
            ->willReturn([]);

        return $event;
    }
}

/**
 * Тестовый агрегат для использования RecordsEvents trait.
 * 
 * Предоставляет публичный метод для триггера события (в реальных агрегатах apply protected).
 */
class TestAggregate
{
    use RecordsEvents;

    /**
     * Публичный метод для триггера события (только для тестов).
     */
    public function triggerEvent(DomainEventInterface $event): void
    {
        $this->apply($event);
    }
}

