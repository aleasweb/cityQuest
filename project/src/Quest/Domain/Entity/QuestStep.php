<?php

declare(strict_types=1);

namespace App\Quest\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Quest Step (Checkpoint) Entity
 * Represents a single checkpoint in a quest with geolocation validation
 */
#[ORM\Entity]
#[ORM\Table(name: 'quest_steps')]
#[ORM\UniqueConstraint(name: 'quest_steps_quest_number_unique', columns: ['quest_id', 'number'])]
#[ORM\Index(name: 'idx_quest_steps_quest_id', columns: ['quest_id'])]
#[ORM\Index(name: 'idx_quest_steps_status', columns: ['status'])]
class QuestStep
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(type: 'uuid')]
    private Uuid $questId;

    #[ORM\Column(type: 'integer')]
    private int $number;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $text = null;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $audioUrl = null;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $videoUrl = null;

    #[ORM\Column(type: 'float')]
    private float $lat;

    #[ORM\Column(type: 'float')]
    private float $lng;

    #[ORM\Column(type: 'integer')]
    private int $radius;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private int $status = 1;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        Uuid $questId,
        int $number,
        float $lat,
        float $lng,
        int $radius
    ) {
        $this->questId = $questId;
        $this->number = $number;
        $this->lat = $lat;
        $this->lng = $lng;
        $this->radius = $radius;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getQuestId(): Uuid
    {
        return $this->questId;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
        $this->touch();
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): void
    {
        $this->text = $text;
        $this->touch();
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): void
    {
        $this->imageUrl = $imageUrl;
        $this->touch();
    }

    public function getAudioUrl(): ?string
    {
        return $this->audioUrl;
    }

    public function setAudioUrl(?string $audioUrl): void
    {
        $this->audioUrl = $audioUrl;
        $this->touch();
    }

    public function getVideoUrl(): ?string
    {
        return $this->videoUrl;
    }

    public function setVideoUrl(?string $videoUrl): void
    {
        $this->videoUrl = $videoUrl;
        $this->touch();
    }

    public function getLat(): float
    {
        return $this->lat;
    }

    public function getLng(): float
    {
        return $this->lng;
    }

    public function getRadius(): int
    {
        return $this->radius;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
        $this->touch();
    }

    public function isActive(): bool
    {
        return $this->status === 1;
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
     * Convert to array for API responses
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'title' => $this->title,
            'text' => $this->text,
            'imageUrl' => $this->imageUrl,
            'audioUrl' => $this->audioUrl,
            'videoUrl' => $this->videoUrl,
            'coordinates' => [
                'lat' => $this->lat,
                'lng' => $this->lng,
            ],
            'radius' => $this->radius,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
