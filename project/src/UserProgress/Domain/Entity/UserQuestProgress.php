<?php

declare(strict_types=1);

namespace App\UserProgress\Domain\Entity;

use App\UserProgress\Domain\Exception\InvalidQuestStatusException;
use App\UserProgress\Domain\ValueObject\QuestStatus;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'user_quest_progress')]
class UserQuestProgress
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column(type: 'uuid')]
    private Uuid $userId;

    #[ORM\Column(type: 'uuid')]
    private Uuid $questId;

    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'active'])]
    private string $status;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isLiked = false;

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
        $this->status = QuestStatus::ACTIVE->value;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
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

    public function isLiked(): bool
    {
        return $this->isLiked;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Start the quest (or resume from paused)
     */
    public function start(): void
    {
        $currentStatus = $this->getStatus();
        
        if ($currentStatus === QuestStatus::COMPLETED) {
            throw InvalidQuestStatusException::cannotTransition(
                $this->questId,
                $currentStatus,
                QuestStatus::ACTIVE
            );
        }

        $this->status = QuestStatus::ACTIVE->value;
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Pause the active quest
     */
    public function pause(): void
    {
        $currentStatus = $this->getStatus();
        
        if ($currentStatus !== QuestStatus::ACTIVE) {
            throw InvalidQuestStatusException::cannotPause($this->questId, $currentStatus);
        }

        $this->status = QuestStatus::PAUSED->value;
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Complete the active quest
     */
    public function complete(): void
    {
        $currentStatus = $this->getStatus();
        
        if ($currentStatus !== QuestStatus::ACTIVE) {
            throw InvalidQuestStatusException::cannotComplete($this->questId, $currentStatus);
        }

        $this->status = QuestStatus::COMPLETED->value;
        $this->completedAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Toggle like status
     */
    public function like(): void
    {
        $this->isLiked = true;
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Unlike the quest
     */
    public function unlike(): void
    {
        $this->isLiked = false;
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Toggle like status
     */
    public function toggleLike(): bool
    {
        $this->isLiked = !$this->isLiked;
        $this->updatedAt = new \DateTimeImmutable();
        
        return $this->isLiked;
    }

    /**
     * Check if quest is active
     */
    public function isActive(): bool
    {
        return $this->getStatus() === QuestStatus::ACTIVE;
    }

    /**
     * Check if quest is completed
     */
    public function isCompleted(): bool
    {
        return $this->getStatus() === QuestStatus::COMPLETED;
    }

    /**
     * Check if quest is paused
     */
    public function isPaused(): bool
    {
        return $this->getStatus() === QuestStatus::PAUSED;
    }

    /**
     * Convert entity to array for API response
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => (string) $this->id,
            'userId' => (string) $this->userId,
            'questId' => (string) $this->questId,
            'status' => $this->status,
            'isLiked' => $this->isLiked,
            'completedAt' => $this->completedAt?->format('Y-m-d H:i:s'),
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
