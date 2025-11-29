<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * Trait для управления EntityManager в тестах.
 */
trait EntityManagerTrait
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
}

