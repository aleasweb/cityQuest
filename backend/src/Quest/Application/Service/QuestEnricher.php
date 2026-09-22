<?php

declare(strict_types=1);

namespace App\Quest\Application\Service;

use App\User\Domain\Entity\User;
use App\UserProgress\Domain\Repository\UserQuestProgressRepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Uid\Uuid;

/**
 * Отвечает за обогащение данных квеста перед отправкой на Presentation Layer.
 * Применяет маппинг городов и добавляет персонализированные данные (лайки, прогресс).
 */
final class QuestEnricher
{
    public function __construct(
        private readonly QuestLikeService $questLikeService,
        private readonly UserQuestProgressRepositoryInterface $progressRepository,
        #[Autowire('%app.cities%')] private readonly array $cities,
    ) {
    }

    /**
     * Обогащает список квестов.
     *
     * @param array<array<string, mixed>> $quests
     * @return array<array<string, mixed>>
     */
    public function enrichList(array $quests, ?User $user): array
    {
        if (empty($quests)) {
            return [];
        }

        $likedMap = [];
        if ($user !== null) {
            $questIds = array_map(fn(array $q) => Uuid::fromString($q['id']), $quests);
            $likedMap = $this->questLikeService->getLikedStatusMap($user->getId(), $questIds);
        }

        foreach ($quests as &$quest) {
            $quest = $this->applyCityMapping($quest);
            $quest['isLikedByCurrentUser'] = $likedMap[$quest['id']] ?? false;
        }

        return $quests;
    }

    /**
     * Обогащает один квест.
     *
     * @param array<string, mixed> $quest
     * @return array<string, mixed>
     */
    public function enrichSingle(array $quest, ?User $user): array
    {
        $quest = $this->applyCityMapping($quest);

        $isStarted = false;
        $isLiked = false;
        $status = null;

        if ($user !== null) {
            $questId = Uuid::fromString($quest['id']);
            $progress = $this->progressRepository->findByUserIdAndQuestId($user->getId(), $questId);

            $isStarted = $progress !== null;
            $isLiked = $this->questLikeService->isLiked($user->getId(), $questId);
            $status = $progress?->getStatus()->value ?? null;
        }

        $quest['isStartedByCurrentUser'] = $isStarted;
        $quest['isLikedByCurrentUser'] = $isLiked;
        $quest['questStatus'] = $status;

        return $quest;
    }

    /**
     * @param array<string, mixed> $quest
     * @return array<string, mixed>
     */
    private function applyCityMapping(array $quest): array
    {
        if (isset($quest['city']) && isset($this->cities[$quest['city']])) {
            $quest['city'] = $this->cities[$quest['city']];
        }

        return $quest;
    }
}
