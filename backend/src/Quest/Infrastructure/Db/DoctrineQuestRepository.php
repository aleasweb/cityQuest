<?php

declare(strict_types=1);

namespace App\Quest\Infrastructure\Db;

use App\Quest\Domain\Entity\Quest;
use App\Quest\Domain\Repository\QuestRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

final class DoctrineQuestRepository implements QuestRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function findById(Uuid $id): ?Quest
    {
        return $this->entityManager->find(Quest::class, $id);
    }

    public function findAll(array $filters = [], array $sort = [], int $limit = 20, int $offset = 0): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('q')
            ->from(Quest::class, 'q');

        // Apply filters
        if (!empty($filters['city'])) {
            $qb->andWhere('q.city = :city')
                ->setParameter('city', $filters['city']);
        }

        if (!empty($filters['difficulty'])) {
            $qb->andWhere('q.difficulty = :difficulty')
                ->setParameter('difficulty', $filters['difficulty']);
        }

        if (!empty($filters['author'])) {
            $qb->andWhere('q.author = :author')
                ->setParameter('author', $filters['author']);
        }

        if (isset($filters['is_popular'])) {
            $qb->andWhere('q.isPopular = :isPopular')
                ->setParameter('isPopular', (bool) $filters['is_popular']);
        }

        // Apply sorting
        if (!empty($sort['field']) && !empty($sort['direction'])) {
            $allowedFields = ['createdAt', 'likesCount', 'durationMinutes'];
            $allowedDirections = ['ASC', 'DESC'];
            
            if (in_array($sort['field'], $allowedFields, true) && 
                in_array(strtoupper($sort['direction']), $allowedDirections, true)) {
                $qb->orderBy('q.' . $sort['field'], strtoupper($sort['direction']));
            }
        } else {
            // Default sorting by created date
            $qb->orderBy('q.createdAt', 'DESC');
        }

        // Apply pagination
        $qb->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    public function count(array $filters = []): int
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COUNT(q.id)')
            ->from(Quest::class, 'q');

        // Apply same filters as findAll
        if (!empty($filters['city'])) {
            $qb->andWhere('q.city = :city')
                ->setParameter('city', $filters['city']);
        }

        if (!empty($filters['difficulty'])) {
            $qb->andWhere('q.difficulty = :difficulty')
                ->setParameter('difficulty', $filters['difficulty']);
        }

        if (!empty($filters['author'])) {
            $qb->andWhere('q.author = :author')
                ->setParameter('author', $filters['author']);
        }

        if (isset($filters['is_popular'])) {
            $qb->andWhere('q.isPopular = :isPopular')
                ->setParameter('isPopular', (bool) $filters['is_popular']);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function findNearby(float $latitude, float $longitude, float $radiusKm = 10, int $limit = 20): array
    {
        // TODO потенциально тяжелый запрос, продумать оптимизацию
        // Haversine formula для вычисления расстояния
                $sql = '
            SELECT * FROM (
                SELECT q.*, 
                    (6371 * acos(
                        cos(radians(:lat)) * 
                        cos(radians(q.latitude)) * 
                        cos(radians(q.longitude) - radians(:lng)) + 
                        sin(radians(:lat)) * 
                        sin(radians(q.latitude))
                    )) AS distance
                FROM quests q
                WHERE q.latitude IS NOT NULL 
                  AND q.longitude IS NOT NULL
            ) AS quest_with_distance
            WHERE distance <= :radius
            ORDER BY distance ASC
            LIMIT :limit
        ';

        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->bindValue('lat', $latitude);
        $stmt->bindValue('lng', $longitude);
        $stmt->bindValue('radius', $radiusKm);
        $stmt->bindValue('limit', $limit);
        $result = $stmt->executeQuery();

        $rows = $result->fetchAllAssociative();
        
        // Преобразуем результаты в Quest entities
        $quests = [];
        foreach ($rows as $row) {
            $quest = $this->entityManager->find(Quest::class, Uuid::fromString($row['id']));
            if ($quest !== null) {
                $quests[] = $quest;
            }
        }

        return $quests;
    }

    public function incrementLikesCount(Uuid $id): void
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->update(Quest::class, 'q')
            ->set('q.likesCount', 'q.likesCount + 1')
            ->where('q.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->execute();
    }

    public function decrementLikesCount(Uuid $id): void
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->update(Quest::class, 'q')
            ->set('q.likesCount', 'q.likesCount - 1')
            ->where('q.id = :id')
            ->andWhere('q.likesCount > 0')
            ->setParameter('id', $id)
            ->getQuery()
            ->execute();
    }

    public function save(Quest $quest): void
    {
        $this->entityManager->persist($quest);
        $this->entityManager->flush();
    }
}
