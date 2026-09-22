<?php

declare(strict_types=1);

namespace App\Quest\Presentation\Controller;

use App\Quest\Application\Service\QuestService;
use App\Quest\Application\Service\QuestListService;
use App\Quest\Application\Service\QuestLikeService;
use App\Quest\Application\Service\QuestEnricher;
use App\Quest\Domain\Exception\QuestNotFoundException;
use App\Quest\Domain\Repository\QuestRepositoryInterface;
use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\UserProgress\Domain\Exception\QuestNotStartedException;
use App\UserProgress\Domain\Repository\UserQuestProgressRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class QuestController extends AbstractController
{
    public function __construct(
        private QuestService $questService,
        private QuestLikeService $questLikeService,
        private QuestListService $questListService,
        private QuestEnricher $questEnricher,
        private QuestRepositoryInterface $questRepository,
        private UserRepositoryInterface $userRepository,
        private UserQuestProgressRepositoryInterface $progressRepository,
    ) {
    }

    private function getCurrentUser(): ?User
    {
        $securityUser = $this->getUser();
        if (!$securityUser) {
            return null;
        }
        return $this->userRepository->findByUsername($securityUser->getUserIdentifier());
    }

    /**
     * Get list of quests with filters and pagination (public endpoint).
     * 
     * GET /api/quests?city=Moscow&difficulty=easy&sort=likesCount&direction=DESC&limit=20&offset=0
     */
    #[Route('/api/quests', name: 'api_quests_list', methods: ['GET'])]
    public function getQuests(Request $request): JsonResponse
    {
        $filters = [];
        
        if ($city = $request->query->get('city')) {
            $filters['city'] = $city;
        }
        if ($difficulty = $request->query->get('difficulty')) {
            $filters['difficulty'] = $difficulty;
        }
        if ($author = $request->query->get('author')) {
            $filters['author'] = $author;
        }
        if ($request->query->has('is_popular')) {
            $filters['is_popular'] = $request->query->getBoolean('is_popular');
        }

        $sortField = $request->query->get('sort');
        $sortDirection = $request->query->get('direction', 'DESC');
        $limit = $request->query->getInt('limit', 20);
        $offset = $request->query->getInt('offset', 0);

        $result = $this->questListService->getQuests($filters, $sortField, $sortDirection, $limit, $offset);

        $result['data'] = $this->questEnricher->enrichList($result['data'], $this->getCurrentUser());

        return $this->json($result);
    }

    /**
     * Get nearby quests based on geolocation (public endpoint).
     * 
     * GET /api/quests/nearby?lat=55.7558&lng=37.6173&radius=10&limit=20
     */
    #[Route('/api/quests/nearby', name: 'api_quests_nearby', methods: ['GET'])]
    public function getNearbyQuests(Request $request): JsonResponse
    {
        $latitude = $request->query->get('lat');
        $longitude = $request->query->get('lng');
        $radius = (float) $request->query->get('radius', 10);
        $limit = $request->query->getInt('limit', 20);

        if ($latitude === null || $longitude === null) {
            return $this->json(
                ['error' => 'Latitude (lat) and longitude (lng) parameters are required'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $lat = (float) $latitude;
        $lng = (float) $longitude;
        
        $result = $this->questListService->getNearbyQuests($lat, $lng, $radius, $limit);
        
        $result['data'] = $this->questEnricher->enrichList($result['data'], $this->getCurrentUser());

        return $this->json($result);
    }

    /**
     * Получить квест по ID (публичный endpoint).
     * Для авторизованных пользователей добавляет isLikedByCurrentUser.
     */
    #[Route('/api/quests/{id}', name: 'api_quests_get', methods: ['GET'])]
    public function getQuest(string $id): JsonResponse
    {
        try {
            $questId = Uuid::fromString($id);
        } catch (\InvalidArgumentException) {
            return $this->json(['error' => 'Invalid quest ID format'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $quest = $this->questService->getQuestById($questId);
        } catch (QuestNotFoundException) {
            return $this->json(['error' => 'Quest not found'], Response::HTTP_NOT_FOUND);
        }

        $quest = $this->questEnricher->enrichSingle($quest, $this->getCurrentUser());

        return $this->json(['data' => $quest]);
    }

    /**
     * Toggle like for a quest (requires authentication).
     * 
     * POST /api/quests/{id}/like
     */
    #[Route('/api/quests/{id}/like', name: 'api_quests_like', methods: ['POST'])]
    public function toggleLike(string $id): JsonResponse
    {
        $questId = Uuid::fromString($id);

        $user = $this->getCurrentUser();
        if (!$user) {
            return $this->json(
                ['error' => 'Authentication required'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        // Сначала проверяем существование квеста
        $quest = $this->questRepository->findById($questId);
        if (!$quest) {
            return $this->json(['error' => 'Quest not found'], Response::HTTP_NOT_FOUND);
        }

        // Затем проверяем что квест есть в прогрессе пользователя (в любом статусе: active, paused, completed)
        $progress = $this->progressRepository->findByUserIdAndQuestId($user->getId(), $questId);
        if (!$progress) {
            return $this->json(['error' => "Quest must be in progress to be liked"], Response::HTTP_FORBIDDEN);
        }

        $result = $this->questLikeService->toggleLike($user->getId(), $questId);

        return $this->json([
            'message' => $result['liked'] ? 'Quest liked' : 'Quest unliked',
            'data' => $result,
        ]);
    }
}
