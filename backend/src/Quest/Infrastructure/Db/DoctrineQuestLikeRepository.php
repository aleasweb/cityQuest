<?php

declare(strict_types=1);

namespace App\Quest\Infrastructure\Db;

use App\Quest\Domain\Entity\QuestLike;
use App\Quest\Domain\Repository\QuestLikeRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

final class DoctrineQuestLikeRepository implements QuestLikeRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function save(QuestLike $like): void
    {
        $this->entityManager->persist($like);
        $this->entityManager->flush();
    }

    public function remove(QuestLike $like): void
    {
        $this->entityManager->remove($like);
        $this->entityManager->flush();
    }

    public function findByUserAndQuest(Uuid $userId, Uuid $questId): ?QuestLike
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('ql')
            ->from(QuestLike::class, 'ql')
            ->where('ql.userId = :userId')
            ->andWhere('ql.questId = :questId')
            ->setParameter('userId', $userId)
            ->setParameter('questId', $questId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countByQuest(Uuid $questId): int
    {
        return (int) $this->entityManager
            ->createQueryBuilder()
            ->select('COUNT(ql.id)')
            ->from(QuestLike::class, 'ql')
            ->where('ql.questId = :questId')
            ->setParameter('questId', $questId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findByUser(Uuid $userId): array
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('ql')
            ->from(QuestLike::class, 'ql')
            ->where('ql.userId = :userId')
            ->orderBy('ql.createdAt', 'DESC')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    public function findLikedQuestIds(Uuid $userId, array $questIds): array
    {
        if (empty($questIds)) {
            return [];
        }

        $result = $this->entityManager
            ->createQueryBuilder()
            ->select('ql.questId')
            ->from(QuestLike::class, 'ql')
            ->where('ql.userId = :userId')
            ->andWhere('ql.questId IN (:questIds)')
            ->setParameter('userId', $userId)
            ->setParameter('questIds', $questIds)
            ->getQuery()
            ->getResult();

        // Extract UUIDs from result array
        return array_map(fn($item) => $item['questId'], $result);
    }
}

