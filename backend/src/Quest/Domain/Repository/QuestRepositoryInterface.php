<?php

declare(strict_types=1);

namespace App\Quest\Domain\Repository;

use App\Quest\Domain\Entity\Quest;
use Symfony\Component\Uid\Uuid;

interface QuestRepositoryInterface
{
    /**
     * Найти квест по ID.
     */
    public function findById(Uuid $id): ?Quest;

    /**
     * Найти все квесты с фильтрами, сортировкой и пагинацией.
     *
     * @param array<string, mixed> $filters
     * @param array<string, string> $sort
     * @return Quest[]
     */
    public function findAll(array $filters = [], array $sort = [], int $limit = 20, int $offset = 0): array;

    /**
     * Подсчитать количество квестов с фильтрами.
     *
     * @param array<string, mixed> $filters
     */
    public function count(array $filters = []): int;

    /**
     * Найти квесты рядом с координатами.
     *
     * @return Quest[]
     */
    public function findNearby(float $latitude, float $longitude, float $radiusKm = 10, int $limit = 20): array;

    /**
     * Увеличить счетчик лайков.
     */
    public function incrementLikesCount(Uuid $id): void;

    /**
     * Уменьшить счетчик лайков.
     */
    public function decrementLikesCount(Uuid $id): void;

    /**
     * Сохранить квест.
     */
    public function save(Quest $quest): void;
}
