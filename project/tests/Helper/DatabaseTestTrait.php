<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * Trait для управления EntityManager и очистки базы данных в тестах.
 */
trait DatabaseTestTrait
{
    private ?EntityManagerInterface $entityManager = null;

    /**
     * Получает EntityManager из контейнера.
     *
     * @param KernelBrowser|null $client Опциональный client для получения EntityManager из его контейнера
     * @return EntityManagerInterface
     */
    protected function getEntityManager(?KernelBrowser $client = null): EntityManagerInterface
    {
        if (!$this->entityManager) {
            if ($client === null) {
                $kernel = self::bootKernel();
                $this->entityManager = $kernel->getContainer()
                    ->get('doctrine')
                    ->getManager();
            } else {
                $this->entityManager = $client->getContainer()
                    ->get('doctrine')
                    ->getManager();
            }
        }

        return $this->entityManager;
    }

    /**
     * Закрывает EntityManager после теста.
     * Должен вызываться в tearDown() метод тестового класса.
     */
    protected function closeEntityManager(): void
    {
        if ($this->entityManager) {
            $this->entityManager->close();
            $this->entityManager = null;
        }
    }

    /**
     * Очищает основные таблицы проекта перед тестом.
     * Рекомендуется вызывать в setUp() методе.
     */
    protected function cleanupDatabase(): void
    {
        $this->clearTables(['quests', 'users', 'user_quest_progress']);
    }

    /**
     * Очищает конкретные таблицы.
     * 
     * @param array $tableNames Массив имен таблиц для очистки
     */
    protected function clearTables(array $tableNames): void
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        
        // Очищаем указанные таблицы (PostgreSQL)
        foreach ($tableNames as $tableName) {
            try {
                $connection->executeStatement("TRUNCATE TABLE \"{$tableName}\" RESTART IDENTITY CASCADE");
            } catch (\Exception $e) {
                // Если таблица не существует, пропускаем
                if (!str_contains($e->getMessage(), 'does not exist')) {
                    throw $e;
                }
            }
        }
    }
}

