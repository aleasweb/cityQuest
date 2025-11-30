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
     * Find all progress records for a user
     *
     * @return UserQuestProgress[]
     */
    public function findByUserId(Uuid $userId): array;

    /**
     * Find active quest for a user
     */
    public function findActiveByUserId(Uuid $userId): ?UserQuestProgress;

    /**
     * Find progress records by user ID with filters
     *
     * @param Uuid $userId
     * @param string|null $status Filter by status (active, paused, completed)
     * @param bool|null $liked Filter by liked status
     * @return UserQuestProgress[]
     */
    public function findByUserIdWithFilters(Uuid $userId, ?string $status = null, ?bool $liked = null): array;

    /**
     * Save progress entity
     */
    public function save(UserQuestProgress $progress): void;

    /**
     * Delete progress entity
     */
    public function delete(UserQuestProgress $progress): void;
}
