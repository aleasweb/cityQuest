<?php

declare(strict_types=1);

namespace App\UserProgress\Presentation\Controller;

use App\Quest\Domain\Exception\QuestNotFoundException;
use App\UserProgress\Application\Service\UserProgressService;
use App\UserProgress\Domain\Exception\ActiveQuestExistsException;
use App\UserProgress\Domain\Exception\InvalidQuestStatusException;
use App\UserProgress\Domain\Exception\ProgressNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api/user/progress', name: 'api_user_progress_')]
class UserProgressController extends AbstractController
{
    public function __construct(
        private readonly UserProgressService $progressService
    ) {
    }

    /**
     * Get user progress with optional filters
     * 
     * GET /api/user/progress?status=active&liked=true
     */
    #[Route('', name: 'get', methods: ['GET'])]
    public function getUserProgress(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        $userId = $user->getId();

        $status = $request->query->get('status');
        $liked = $request->query->get('liked');
        
        // Convert liked string to bool
        $likedBool = null;
        if ($liked === 'true') {
            $likedBool = true;
        } elseif ($liked === 'false') {
            $likedBool = false;
        }

        // Validate status
        if ($status !== null && !in_array($status, ['active', 'paused', 'completed'], true)) {
            return $this->json([
                'error' => 'Invalid status. Must be one of: active, paused, completed'
            ], Response::HTTP_BAD_REQUEST);
        }

        $result = $this->progressService->getUserProgress($userId, $status, $likedBool);

        return $this->json($result);
    }

    /**
     * Start a quest
     * 
     * POST /api/user/progress/{questId}/start
     */
    #[Route('/{questId}/start', name: 'start', methods: ['POST'])]
    public function startQuest(string $questId): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user) {
                return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
            }
            $userId = $user->getId();
            $questUuid = Uuid::fromString($questId);

            $progress = $this->progressService->startQuest($userId, $questUuid);

            return $this->json([
                'message' => 'Quest started successfully',
                'data' => $progress->toArray(),
            ], Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'error' => 'Invalid quest ID format'
            ], Response::HTTP_BAD_REQUEST);
        } catch (QuestNotFoundException $e) {
            return $this->json([
                'error' => 'Quest not found'
            ], Response::HTTP_NOT_FOUND);
        } catch (ActiveQuestExistsException $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Failed to start quest',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Pause an active quest
     * 
     * PATCH /api/user/progress/{questId}/pause
     */
    #[Route('/{questId}/pause', name: 'pause', methods: ['PATCH'])]
    public function pauseQuest(string $questId): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user) {
                return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
            }
            $userId = $user->getId();
            $questUuid = Uuid::fromString($questId);

            $progress = $this->progressService->pauseQuest($userId, $questUuid);

            return $this->json([
                'message' => 'Quest paused successfully',
                'data' => $progress->toArray(),
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'error' => 'Invalid quest ID format'
            ], Response::HTTP_BAD_REQUEST);
        } catch (ProgressNotFoundException $e) {
            return $this->json([
                'error' => 'Quest progress not found'
            ], Response::HTTP_NOT_FOUND);
        } catch (InvalidQuestStatusException $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Failed to pause quest',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Complete an active quest
     * 
     * PATCH /api/user/progress/{questId}/complete
     */
    #[Route('/{questId}/complete', name: 'complete', methods: ['PATCH'])]
    public function completeQuest(string $questId): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user) {
                return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
            }
            $userId = $user->getId();
            $questUuid = Uuid::fromString($questId);

            $progress = $this->progressService->completeQuest($userId, $questUuid);

            return $this->json([
                'message' => 'Quest completed successfully',
                'data' => $progress->toArray(),
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'error' => 'Invalid quest ID format'
            ], Response::HTTP_BAD_REQUEST);
        } catch (ProgressNotFoundException $e) {
            return $this->json([
                'error' => 'Quest progress not found'
            ], Response::HTTP_NOT_FOUND);
        } catch (InvalidQuestStatusException $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Failed to complete quest',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
