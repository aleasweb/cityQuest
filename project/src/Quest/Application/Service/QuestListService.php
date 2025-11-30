<?php

declare(strict_types=1);

namespace App\Quest\Application\Service;

use App\Quest\Domain\Repository\QuestRepositoryInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Service for retrieving quest lists
 */
class QuestListService
{
    public function __construct(
        private readonly QuestRepositoryInterface $questRepository
    ) {
    }

    /**
     * Get quests with filters, sorting and pagination
     * 
     * @param array<string, mixed> $filters Available filters: city, difficulty, author, is_popular
     * @param string|null $sortField Field to sort by (createdAt, likesCount, durationMinutes)
     * @param string $sortDirection Sort direction (ASC or DESC)
     * @param int $limit Maximum number of results (max 100)
     * @param int $offset Pagination offset
     * @return array{data: array<array<string, mixed>>, meta: array<string, int>}
     */
    public function getQuests(
        array $filters = [],
        ?string $sortField = null,
        string $sortDirection = 'DESC',
        int $limit = 20,
        int $offset = 0
    ): array {
        // Validate and sanitize inputs
        $limit = min($limit, 100); // Max 100 per page
        $offset = max($offset, 0);
        
        $sort = [];
        if ($sortField !== null) {
            $allowedFields = ['createdAt', 'likesCount', 'durationMinutes'];
            if (in_array($sortField, $allowedFields, true)) {
                $sort = [
                    'field' => $sortField,
                    'direction' => strtoupper($sortDirection) === 'ASC' ? 'ASC' : 'DESC',
                ];
            }
        }

        // Get quests
        $quests = $this->questRepository->findAll($filters, $sort, $limit, $offset);
        
        // Get total count for metadata
        $total = $this->questRepository->count($filters);

        // Convert to array
        $data = array_map(fn($quest) => $quest->toArray(), $quests);

        return [
            'data' => $data,
            'meta' => [
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
                'count' => count($data),
            ],
        ];
    }

    /**
     * Get nearby quests based on geolocation
     * 
     * @param float $latitude
     * @param float $longitude
     * @param float $radiusKm Radius in kilometers (max 100)
     * @param int $limit Maximum number of results
     * @return array{data: array<array<string, mixed>>, meta: array<string, mixed>}
     */
    public function getNearbyQuests(
        float $latitude,
        float $longitude,
        float $radiusKm = 10,
        int $limit = 20
    ): array {
        // Validate inputs
        $radiusKm = min($radiusKm, 100); // Max 100km radius
        $limit = min($limit, 100);

        // Validate coordinates
        if ($latitude < -90 || $latitude > 90) {
            throw new \InvalidArgumentException('Latitude must be between -90 and 90');
        }
        
        if ($longitude < -180 || $longitude > 180) {
            throw new \InvalidArgumentException('Longitude must be between -180 and 180');
        }

        // Get nearby quests
        $quests = $this->questRepository->findNearby($latitude, $longitude, $radiusKm, $limit);

        // Convert to array
        $data = array_map(fn($quest) => $quest->toArray(), $quests);

        return [
            'data' => $data,
            'meta' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'radius' => $radiusKm,
                'count' => count($data),
            ],
        ];
    }

    /**
     * Get a single quest by ID
     */
    public function getQuestById(Uuid $id): ?array
    {
        $quest = $this->questRepository->findById($id);
        
        return $quest?->toArray();
    }
}
