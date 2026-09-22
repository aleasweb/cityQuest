<?php

declare(strict_types=1);

namespace App\Quest\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'quest_likes')]
#[ORM\UniqueConstraint(name: 'unique_user_quest_like', columns: ['user_id', 'quest_id'])]
class QuestLike
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column(type: 'uuid')]
    private Uuid $userId;

    #[ORM\Column(type: 'uuid')]
    private Uuid $questId;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(Uuid $userId, Uuid $questId)
    {
        $this->id = Uuid::v4();
        $this->userId = $userId;
        $this->questId = $questId;
        $this->createdAt = new \DateTimeImmutable();
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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}

