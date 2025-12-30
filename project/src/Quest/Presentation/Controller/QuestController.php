<?php

declare(strict_types=1);

namespace App\Quest\Presentation\Controller;

use App\Quest\Application\Service\QuestService;
use App\Quest\Application\Service\QuestListService;
use App\Quest\Application\Service\QuestLikeService;
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
        private QuestRepositoryInterface $questRepository,
        private UserRepositoryInterface $userRepository,
        private UserQuestProgressRepositoryInterface $progressRepository,
    ) {
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

        try {
            $result = $this->questListService->getQuests($filters, $sortField, $sortDirection, $limit, $offset);

            // Получаем текущего пользователя для проверки лайков
            $securityUser = $this->getUser();
            $likedMap = [];
            if ($securityUser) {
                $user = $this->userRepository->findByUsername($securityUser->getUserIdentifier());
                if ($user) {
                    // Собираем все quest_id для batch-запроса
                    $questIds = array_map(
                        fn($quest) => \Symfony\Component\Uid\Uuid::fromString($quest['id']),
                        $result['data']
                    );
                    // Один запрос для всех квестов
                    $likedMap = $this->questLikeService->getLikedStatusMap($user->getId(), $questIds);
                }
            }
            
            // Получаем маппинг городов и преобразуем названия
            $cities = $this->getParameter('app.cities');
            foreach ($result['data'] as &$quest) {
                if (isset($quest['city']) && isset($cities[$quest['city']])) {
                    $quest['city'] = $cities[$quest['city']];
                }
                
                // Добавляем isLikedByCurrentUser из предварительно загруженного map
                $quest['isLikedByCurrentUser'] = $likedMap[$quest['id']] ?? false;
            }
            
            return $this->json($result);
        } catch (\Exception $e) {
            return $this->json(
                ['error' => 'An error occurred while retrieving quests'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
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

        $latitude = (float) $latitude;
        $longitude = (float) $longitude;

        try {
            $lat = (float) $latitude;
            $lng = (float) $longitude;
            
            $result = $this->questListService->getNearbyQuests($lat, $lng, $radius, $limit);
            
            // Получаем текущего пользователя для проверки лайков
            $securityUser = $this->getUser();
            $likedMap = [];
            if ($securityUser) {
                $user = $this->userRepository->findByUsername($securityUser->getUserIdentifier());
                if ($user) {
                    // Собираем все quest_id для batch-запроса
                    $questIds = array_map(
                        fn($quest) => \Symfony\Component\Uid\Uuid::fromString($quest['id']),
                        $result['data']
                    );
                    // Один запрос для всех квестов
                    $likedMap = $this->questLikeService->getLikedStatusMap($user->getId(), $questIds);
                }
            }
            
            // Получаем маппинг городов и преобразуем названия
            $cities = $this->getParameter('app.cities');
            foreach ($result['data'] as &$quest) {
                if (isset($quest['city']) && isset($cities[$quest['city']])) {
                    $quest['city'] = $cities[$quest['city']];
                }
                
                // Добавляем isLikedByCurrentUser из предварительно загруженного map
                $quest['isLikedByCurrentUser'] = $likedMap[$quest['id']] ?? false;
            }
            
            return $this->json($result);
        } catch (\InvalidArgumentException $e) {
            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        } catch (\Exception $e) {
            return $this->json(
                ['error' => 'An error occurred while retrieving nearby quests'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Получить квест по ID (публичный endpoint).
     * Для авторизованных пользователей добавляет isLikedByCurrentUser.
     */
    #[Route('/api/quests/{id}', name: 'api_quests_get', methods: ['GET'])]
    public function getQuest(string $id): JsonResponse
    {
        try {
            // Валидация UUID формата
            $questId = Uuid::fromString($id);
        } catch (\InvalidArgumentException $e) {
            return $this->json(
                ['error' => 'Invalid quest ID format'],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $quest = $this->questService->getQuestById($questId);
            
            // Преобразуем название города
            $cities = $this->getParameter('app.cities');
            if ($quest && isset($quest['city']) && isset($cities[$quest['city']])) {
                $quest['city'] = $cities[$quest['city']];
            }
            
            // Проверяем статус квеста для текущего пользователя
            $securityUser = $this->getUser();
            if ($securityUser) {
                // Получаем полный User entity из репозитория
                $user = $this->userRepository->findByUsername($securityUser->getUserIdentifier());
                if ($user) {
                    $progress = $this->progressRepository->findByUserIdAndQuestId($user->getId(), $questId);
                    $quest['isStartedByCurrentUser'] = $progress !== null;
                    $quest['isLikedByCurrentUser'] = $this->questLikeService->isLiked($user->getId(), $questId);
                    $quest['questStatus'] = $progress?->getStatus()->value ?? null;
                } else {
                    $quest['isStartedByCurrentUser'] = false;
                    $quest['isLikedByCurrentUser'] = false;
                    $quest['questStatus'] = null;
                }
            } else {
                $quest['isStartedByCurrentUser'] = false;
                $quest['isLikedByCurrentUser'] = false;
                $quest['questStatus'] = null;
            }
            
            // Оборачиваем в data для консистентности с другими endpoints
            return $this->json(['data' => $quest]);
        } catch (QuestNotFoundException $e) {
            return $this->json(
                ['error' => $e->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        } catch (\Exception $e) {
            return $this->json(
                ['error' => 'An error occurred while retrieving quest'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Toggle like for a quest (requires authentication).
     * 
     * POST /api/quests/{id}/like
     */
    #[Route('/api/quests/{id}/like', name: 'api_quests_like', methods: ['POST'])]
    public function toggleLike(string $id): JsonResponse
    {
        try {
            $questId = Uuid::fromString($id);
        } catch (\InvalidArgumentException $e) {
            return $this->json(
                ['error' => 'Invalid quest ID format'],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $securityUser = $this->getUser();
            if (!$securityUser) {
                return $this->json(
                    ['error' => 'Authentication required'],
                    Response::HTTP_UNAUTHORIZED
                );
            }
            
            // Получаем полный User entity из репозитория
            $user = $this->userRepository->findByUsername($securityUser->getUserIdentifier());
            if (!$user) {
                return $this->json(
                    ['error' => 'User not found'],
                    Response::HTTP_UNAUTHORIZED
                );
            }

            // Сначала проверяем существование квеста
            $quest = $this->questRepository->findById($questId);
            if (!$quest) {
                throw QuestNotFoundException::withId($questId);
            }

            // Затем проверяем что квест есть в прогрессе пользователя (в любом статусе: active, paused, completed)
            $progress = $this->progressRepository->findByUserIdAndQuestId($user->getId(), $questId);
            if (!$progress) {
                throw QuestNotStartedException::forQuest($questId);
            }

            $result = $this->questLikeService->toggleLike($user->getId(), $questId);

            return $this->json([
                'message' => $result['liked'] ? 'Quest liked' : 'Quest unliked',
                'data' => $result,
            ]);
        } catch (QuestNotFoundException $e) {
            return $this->json(
                ['error' => 'Quest not found'],
                Response::HTTP_NOT_FOUND
            );
        } catch (QuestNotStartedException $e) {
            return $this->json(
                ['error' => 'Quest must be in progress to be liked'],
                Response::HTTP_FORBIDDEN
            );
        } catch (\Exception $e) {
            return $this->json(
                ['error' => 'An error occurred while toggling like'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
