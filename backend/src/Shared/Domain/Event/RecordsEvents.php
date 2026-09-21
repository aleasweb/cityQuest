<?php

declare(strict_types=1);

namespace App\Shared\Domain\Event;

/**
 * Trait для агрегатов, поддерживающих генерацию доменных событий.
 * 
 * Предоставляет механизм накопления событий в агрегате с последующим
 * извлечением для персистентности через Event Store.
 * 
 * Использование:
 * ```php
 * class MyAggregate
 * {
 *     use RecordsEvents;
 *     
 *     public function doSomething(): void
 *     {
 *         // Генерация события
 *         $this->apply(new SomethingHappenedEvent(...));
 *     }
 *
 *     protected function mutate(DomainEventInterface $event): void
 *     {
 *          // Изменение состояния
 *          switch ($event::class) {
 *              case QuestStartedEvent::class:
 *                  $this->status = 'active';
 *     }
 * }
 * ```
 */
trait RecordsEvents
{
    /**
     * @var array<DomainEventInterface> Накопленные доменные события
     */
    private array $recordedEvents = [];

    /**
     * Получить накопленные события и очистить список.
     * 
     * Этот метод должен вызываться сервисом после сохранения агрегата
     * для персистентности событий в Event Store.
     */
    public function pull(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];
        
        return $events;
    }

    /**
     * Применить доменное событие к агрегату и записать его в массив накопленных событий,
     * откуда оно будет извлечено через pull() для последующей персистентности.
     * 
     * Метод protected, так как предназначен для использования только
     * внутри агрегата при изменении его состояния.
     */
    protected function apply(DomainEventInterface $event): void
    {
        $this->mutate($event);
        $this->recordedEvents[] = $event;
    }

    /**
     * Мутация агрегата
     * конкретная логика мутации должны быть описана в самом агрегате
     */
    protected function mutate(DomainEventInterface $event): void
    {
    }
}
