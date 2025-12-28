<?php

declare(strict_types=1);

namespace App\UserProgress\Domain\Event;

use App\Platform\ValueObject\Platform;
use Symfony\Component\Uid\Uuid;

/**
 * Событие проверки шага квеста.
 */
final class QuestStepCheckEvent extends AbstractUserQuestProgressEvent
{
    /**
     * @param Uuid $aggregateId ID агрегата UserQuestProgress
     * @param Uuid $userId ID пользователя
     * @param Uuid $questId ID квеста
     * @param \DateTimeImmutable $occurredAt Время события
     * @param Platform $platform Данные о платформе
     * @param float $clientLatitude Широта клиента
     * @param float $clientLongitude Долгота клиента
     * @param float $distanceToPoint Расстояние до точки квеста (в метрах)
     * @param bool $checkPassed Результат проверки условий шага
     */
    public function __construct(
        Uuid $aggregateId,
        Uuid $userId,
        Uuid $questId,
        \DateTimeImmutable $occurredAt,
        Platform $platform,
        private float $clientLatitude,
        private float $clientLongitude,
        private float $distanceToPoint,
        private bool $checkPassed
    ) {
        parent::__construct($aggregateId, $userId, $questId, $occurredAt, $platform);
    }

    public function getEventData(): array
    {
        return [
            'client_latitude' => $this->clientLatitude,
            'client_longitude' => $this->clientLongitude,
            'distance_to_point' => $this->distanceToPoint,
            'check_passed' => $this->checkPassed,
        ];
    }

    public function getClientLatitude(): float
    {
        return $this->clientLatitude;
    }

    public function getClientLongitude(): float
    {
        return $this->clientLongitude;
    }

    public function getDistanceToPoint(): float
    {
        return $this->distanceToPoint;
    }

    public function isCheckPassed(): bool
    {
        return $this->checkPassed;
    }
}

