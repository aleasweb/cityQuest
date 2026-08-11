<?php

declare(strict_types=1);

namespace App\Quest\Infrastructure\Db;

use App\Quest\Domain\Entity\QuestStep;
use App\Quest\Domain\Repository\QuestStepRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

final class DoctrineQuestStepRepository implements QuestStepRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function findByQuestAndNumber(Uuid $questId, int $number): ?QuestStep
    {
        return $this->entityManager->createQueryBuilder()
            ->select('qs')
            ->from(QuestStep::class, 'qs')
            ->where('qs.questId = :questId')
            ->andWhere('qs.number = :number')
            ->setParameter('questId', $questId, 'uuid')
            ->setParameter('number', $number)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findFirstActiveByQuest(Uuid $questId): ?QuestStep
    {
        return $this->entityManager->createQueryBuilder()
            ->select('MIN(qs.number)')
            ->from(QuestStep::class, 'qs')
            ->where('qs.questId = :questId')
            ->andWhere('qs.status = 1')
            ->orderBy('qs.number', 'ASC')
            ->setMaxResults(1)
            ->setParameter('questId', $questId, 'uuid')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findNextActiveByQuestAndNumber(Uuid $questId, int $currentNumber): ?QuestStep
    {
        return $this->entityManager->createQueryBuilder()
            ->select('qs')
            ->from(QuestStep::class, 'qs')
            ->where('qs.questId = :questId')
            ->andWhere('qs.number > :currentNumber')
            ->andWhere('qs.status = 1')
            ->orderBy('qs.number', 'ASC')
            ->setMaxResults(1)
            ->setParameter('questId', $questId, 'uuid')
            ->setParameter('currentNumber', $currentNumber)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countActiveByQuest(Uuid $questId): int
    {
        return (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(qs.id)')
            ->from(QuestStep::class, 'qs')
            ->where('qs.questId = :questId')
            ->andWhere('qs.status = 1')
            ->setParameter('questId', $questId, 'uuid')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function isLastActiveStep(Uuid $questId, int $stepNumber): bool
    {
        $maxStepNumber = $this->entityManager->createQueryBuilder()
            ->select('MAX(qs.number)')
            ->from(QuestStep::class, 'qs')
            ->where('qs.questId = :questId')
            ->andWhere('qs.status = 1')
            ->setParameter('questId', $questId, 'uuid')
            ->getQuery()
            ->getSingleScalarResult();

        return $maxStepNumber !== null && (int) $maxStepNumber === $stepNumber;
    }

    public function save(QuestStep $questStep): void
    {
        $this->entityManager->persist($questStep);
        $this->entityManager->flush();
    }
}
