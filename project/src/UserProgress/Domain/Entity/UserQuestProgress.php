<?php

declare(strict_types=1);

namespace App\UserProgress\Domain\Entity;

use App\Shared\Domain\Event\DomainEventInterface;
use App\Shared\Domain\Event\RecordsEvents;
use App\UserProgress\Domain\Event\AbstractUserQuestProgressEvent;
use App\UserProgress\Domain\Event\QuestAbandonedEvent;
use App\UserProgress\Domain\Event\QuestCompletedEvent;
use App\UserProgress\Domain\Event\QuestPausedEvent;
use App\UserProgress\Domain\Event\QuestResumedEvent;
use App\UserProgress\Domain\Event\QuestStartedEvent;
use App\UserProgress\Domain\Event\QuestStepCheckEvent;
use App\UserProgress\Domain\Exception\InvalidQuestStatusException;
use App\UserProgress\Domain\ValueObject\QuestStatus;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'user_quest_progress')]
class UserQuestProgress
{
    use RecordsEvents;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column(type: 'uuid')]
    private Uuid $userId;

    #[ORM\Column(type: 'uuid')]
    private Uuid $questId;

    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'active'])]
    private string $status;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(Uuid $userId, Uuid $questId)
    {
        $this->id = Uuid::v4();
        $this->userId = $userId;
        $this->questId = $questId;
        $this->status = QuestStatus::NEW->value;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getUserId(): Uuid
    {
        return $this->userId;
    }

    public function getQuestId(): Uuid
    {
        return $this->questId;
    }

    public function getStatus(): QuestStatus
    {
        return QuestStatus::from($this->status);
    }

    public function getCompletedAt(): ?DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function start(): void
    {
        $currentStatus = $this->getStatus();

        if (!$currentStatus->canTransitionTo(QuestStatus::ACTIVE)) {
            throw InvalidQuestStatusException::cannotTransition(
                $this->questId,
                $currentStatus,
                QuestStatus::ACTIVE
            );
        }

        $this->apply(new QuestStartedEvent(
            $this->id,
            $this->userId,
            $this->questId
        ));

    }

    public function pause(): void
    {
        $currentStatus = $this->getStatus();
        
        if (!$currentStatus->canTransitionTo(QuestStatus::PAUSED)) {
            throw InvalidQuestStatusException::cannotTransition(
                $this->questId,
                $currentStatus,
                QuestStatus::PAUSED
            );
        }

        $this->apply(new QuestPausedEvent(
            $this->id,
            $this->userId,
            $this->questId
        ));
    }

    public function resume(): void
    {
        $currentStatus = $this->getStatus();
        if (!$currentStatus->canTransitionTo(QuestStatus::ACTIVE)) {
            throw InvalidQuestStatusException::cannotTransition(
                $this->questId,
                $currentStatus,
                QuestStatus::ACTIVE
            );
        }

        $this->apply(new QuestResumedEvent(
            $this->id,
            $this->userId,
            $this->questId
        ));

    }

    public function complete(): void
    {
        $currentStatus = $this->getStatus();
        
        if (!$currentStatus->canTransitionTo(QuestStatus::COMPLETED)) {
            throw InvalidQuestStatusException::cannotTransition(
                $this->questId,
                $currentStatus,
                QuestStatus::COMPLETED
            );
        }

        // @todo проверить условие завершения квеста

        $this->apply(new QuestCompletedEvent(
            $this->id,
            $this->userId,
            $this->questId
        ));
    }

    /**
     * удаляет квест из списка пользователя
     */
    public function abandon(): void
    {
        $currentStatus = $this->getStatus();

        if (!$currentStatus->canTransitionTo(QuestStatus::NEW)) {
            throw InvalidQuestStatusException::cannotTransition(
                $this->questId,
                $currentStatus,
                QuestStatus::PAUSED
            );
        }

        $this->apply(new QuestAbandonedEvent(
            $this->id,
            $this->userId,
            $this->questId
        ));
    }

    protected function mutate(DomainEventInterface $event): void
    {
        // Обрабатываем только события UserQuestProgress
        if (!$event instanceof AbstractUserQuestProgressEvent) {
            return;
        }
        
        $now = new DateTimeImmutable();
        $this->updatedAt = $now;

        switch ($event::class) {
            case QuestStartedEvent::class:
                $this->status = QuestStatus::ACTIVE->value;
                break;
            case QuestPausedEvent::class:
                $this->status = QuestStatus::PAUSED->value;
                break;
            case QuestAbandonedEvent::class:
                // @todo сбрасываем прогресс
                $this->status = QuestStatus::PAUSED->value;
                break;
            case QuestCompletedEvent::class:
                $this->status = QuestStatus::COMPLETED->value;
                $this->completedAt = $now;
                break;
            case QuestResumedEvent::class:
                $this->status = QuestStatus::ACTIVE->value;
                break;
            case QuestStepCheckEvent::class:
                break;
        };
    }


    public function toArray(): array
    {
        return [
            'id' => (string) $this->id,
            'userId' => (string) $this->userId,
            'questId' => (string) $this->questId,
            'status' => $this->status,
            'completedAt' => $this->completedAt?->format('Y-m-d H:i:s'),
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
