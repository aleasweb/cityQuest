<?php

declare(strict_types=1);

namespace App\UserProgress\Infrastructure\Db;

use App\UserProgress\Domain\Entity\UserQuestProgress;
use App\UserProgress\Domain\Repository\UserQuestProgressRepositoryInterface;
use App\UserProgress\Domain\ValueObject\QuestStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class DoctrineUserQuestProgressRepository implements UserQuestProgressRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function findById(Uuid $id): ?UserQuestProgress
    {
        return $this->entityManager->find(UserQuestProgress::class, $id);
    }

    public function findByUserIdAndQuestId(Uuid $userId, Uuid $questId): ?UserQuestProgress
    {
        return $this->entityManager->getRepository(UserQuestProgress::class)
            ->findOneBy([
                'userId' => $userId,
                'questId' => $questId,
            ]);
    }

    public function findByUserId(Uuid $userId): array
    {
        return $this->entityManager->getRepository(UserQuestProgress::class)
            ->findBy(['userId' => $userId], ['createdAt' => 'DESC']);
    }

    public function findActiveByUserId(Uuid $userId): ?UserQuestProgress
    {
        return $this->entityManager->getRepository(UserQuestProgress::class)
            ->findOneBy([
                'userId' => $userId,
                'status' => QuestStatus::ACTIVE->value,
            ]);
    }

    public function findByUserIdAndStatus(Uuid $userId, ?string $status = null): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        
        $qb->select('p')
            ->from(UserQuestProgress::class, 'p')
            ->where('p.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('p.createdAt', 'DESC');

        if ($status !== null) {
            $qb->andWhere('p.status = :status')
                ->setParameter('status', $status);
        }

        return $qb->getQuery()->getResult();
    }

    public function save(UserQuestProgress $progress): void
    {
        $this->entityManager->persist($progress);
        $this->entityManager->flush();
    }

    public function delete(UserQuestProgress $progress): void
    {
        $this->entityManager->remove($progress);
        $this->entityManager->flush();
    }
}
