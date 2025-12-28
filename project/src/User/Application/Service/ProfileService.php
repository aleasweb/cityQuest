<?php

declare(strict_types=1);

namespace App\User\Application\Service;

use App\User\Application\DTO\UpdateProfileRequest;
use App\User\Domain\Entity\User;
use App\User\Domain\Exception\UserAlreadyExistsException;
use App\User\Domain\Exception\UserNotFoundException;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\UserProgress\Domain\Repository\UserQuestProgressRepositoryInterface;
use App\Quest\Domain\Repository\QuestRepositoryInterface;
use App\UserProgress\Domain\ValueObject\QuestStatus;

final class ProfileService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserQuestProgressRepositoryInterface $progressRepository,
        private readonly QuestRepositoryInterface $questRepository,
    ) {
    }

    /**
     * Возвращает полные данные профиля пользователя (с email).
     *
     * @return array<string, mixed>
     */
    public function getProfile(User $user): array
    {
        return [
            'id' => (string) $user->getId(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Возвращает публичные данные профиля пользователя (без email).
     *
     * @return array<string, mixed>
     */
    public function getPublicProfile(string $username): array
    {
        $user = $this->userRepository->findByUsername($username);

        if ($user === null) {
            throw UserNotFoundException::withUsername($username);
        }

        return [
            'id' => (string) $user->getId(),
            'username' => $user->getUsername(),
            'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Возвращает публичный профиль с историей квестов
     *
     * @return array<string, mixed>
     */
    public function getPublicProfileWithQuestHistory(string $username): array
    {
        $user = $this->userRepository->findByUsername($username);

        if ($user === null) {
            throw UserNotFoundException::withUsername($username);
        }

        $profile = [
            'id' => (string) $user->getId(),
            'username' => $user->getUsername(),
            'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        // Получаем активный квест
        $activeQuest = $this->progressRepository->findActiveByUserId($user->getId());
        $profile['activeQuest'] = $activeQuest ? $this->formatQuestProgress($activeQuest) : null;

        // Получаем квесты на паузе
        $pausedQuests = $this->progressRepository->findByUserIdAndStatus($user->getId(), QuestStatus::PAUSED->value);
        $profile['pausedQuests'] = array_map(
            fn($progress) => $this->formatQuestProgress($progress),
            $pausedQuests
        );

        // Получаем 5 последних завершённых квестов
        $completedQuests = $this->progressRepository->findByUserIdAndStatus($user->getId(), QuestStatus::COMPLETED->value);
        // Сортируем по дате завершения (новые первыми) и берём первые 5
        usort($completedQuests, fn($a, $b) => $b->getCompletedAt() <=> $a->getCompletedAt());
        $profile['completedQuests'] = array_map(
            fn($progress) => $this->formatQuestProgress($progress),
            array_slice($completedQuests, 0, 5)
        );

        return $profile;
    }

    /**
     * Форматирует прогресс квеста для ответа
     *
     * @return array<string, mixed>
     */
    private function formatQuestProgress($progress): array
    {
        $quest = $this->questRepository->findById($progress->getQuestId());
        
        return [
            'quest' => [
                'id' => (string) $progress->getQuestId(),
                'title' => $quest?->getTitle() ?? 'Unknown',
                'imageUrl' => $quest?->getImageUrl(),
                'difficulty' => $quest?->getDifficulty(),
                'city' => $quest?->getCity(),
            ],
            'status' => $progress->getStatus()->value,
            'isLiked' => $progress->isLiked(),
            'startedAt' => $progress->getCreatedAt()->format('Y-m-d H:i:s'),
            'completedAt' => $progress->getCompletedAt()?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Обновляет профиль пользователя.
     */
    public function updateProfile(User $user, UpdateProfileRequest $request): User
    {
        // Если email не передан, ничего не обновляем
        if ($request->email === null) {
            return $user;
        }

        // Проверяем, изменился ли email
        if ($request->email !== $user->getEmail()) {
            // Проверяем уникальность нового email
            if ($this->userRepository->emailExists($request->email)) {
                throw UserAlreadyExistsException::withEmail($request->email);
            }

            $user->setEmail($request->email);
            $this->userRepository->save($user);
        }

        return $user;
    }
}
