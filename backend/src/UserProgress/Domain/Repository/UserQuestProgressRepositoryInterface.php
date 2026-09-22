<?php

declare(strict_types=1);

namespace App\UserProgress\Domain\Repository;

use App\UserProgress\Domain\Entity\UserQuestProgress;
use Symfony\Component\Uid\Uuid;

/**
 * Repository interface for UserQuestProgress entity
 */
interface UserQuestProgressRepositoryInterface
{
    /**
     * Find progress by ID
     */
    public function findById(Uuid $id): ?UserQuestProgress;

    /**
     * Find progress by user ID and quest ID
     */
    public function findByUserIdAndQuestId(Uuid $userId, Uuid $questId): ?UserQuestProgress;

    /**
     * @return UserQuestProgress[]
     */
    public function findByUserId(Uuid $userId): array;

    public function findActiveByUserId(Uuid $userId): ?UserQuestProgress;

    /**
     * @return UserQuestProgress[]
     */
    public function findByUserIdAndStatus(Uuid $userId, ?string $status = null): array;

    public function save(UserQuestProgress $progress): void;

    public function delete(UserQuestProgress $progress): void;
}
