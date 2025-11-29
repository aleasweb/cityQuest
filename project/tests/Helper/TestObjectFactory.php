<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use App\Quest\Domain\Entity\Quest;
use App\User\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Фабрика для создания тестовых объектов в БД.
 */
class TestObjectFactory
{
    /**
     * Создает тестовый Quest в базе данных со всеми возможными параметрами.
     */
    public static function createQuest(
        EntityManagerInterface $entityManager,
        string $title,
        ?string $description = null,
        ?string $city = null,
        ?string $difficulty = null,
        ?int $durationMinutes = null,
        ?float $distanceKm = null,
        ?string $imageUrl = null,
        ?string $author = null,
        ?int $likesCount = null,
        ?bool $isPopular = null,
        ?float $latitude = null,
        ?float $longitude = null
    ): Quest {
        $quest = new Quest($title);
        
        if ($description !== null) {
            $quest->setDescription($description);
        }
        if ($city !== null) {
            $quest->setCity($city);
        }
        if ($difficulty !== null) {
            $quest->setDifficulty($difficulty);
        }
        if ($durationMinutes !== null) {
            $quest->setDurationMinutes($durationMinutes);
        }
        if ($distanceKm !== null) {
            $quest->setDistanceKm($distanceKm);
        }
        if ($imageUrl !== null) {
            $quest->setImageUrl($imageUrl);
        }
        if ($author !== null) {
            $quest->setAuthor($author);
        }
        if ($likesCount !== null) {
            $quest->setLikesCount($likesCount);
        }
        if ($isPopular !== null) {
            $quest->setIsPopular($isPopular);
        }
        if ($latitude !== null) {
            $quest->setLatitude($latitude);
        }
        if ($longitude !== null) {
            $quest->setLongitude($longitude);
        }

        $entityManager->persist($quest);
        $entityManager->flush();

        return $quest;
    }

    /**
     * Вспомогательный метод для создания Quest с дефолтными тестовыми значениями.
     */
    public static function createQuestWithDefaults(
        EntityManagerInterface $entityManager,
        string $title
    ): Quest {
        return self::createQuest(
            entityManager: $entityManager,
            title: $title,
            description: 'Test description for integration',
            city: 'Moscow',
            difficulty: 'medium',
            durationMinutes: 90,
            distanceKm: 3.2,
            imageUrl: 'https://example.com/test-image.jpg',
            author: 'Integration Author',
            likesCount: 15,
            isPopular: true
        );
    }

    /**
     * Создает тестового User в базе данных.
     */
    public static function createUser(
        EntityManagerInterface $entityManager,
        string $username,
        ?string $email = null,
        string $password = 'password123',
        array $roles = ['ROLE_USER']
    ): User {
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email ?? $username . '@test.com');
        $user->setPassword(password_hash($password, PASSWORD_BCRYPT));
        $user->setRoles($roles);

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }

    /**
     * Создает тестового User с хешированием пароля через UserPasswordHasher.
     */
    public static function createUserWithHasher(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        string $username,
        ?string $email = null,
        string $password = 'password123',
        array $roles = ['ROLE_USER']
    ): User {
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email ?? $username . '@test.com');
        $user->setRoles($roles);

        $hashedPassword = $passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }
}

