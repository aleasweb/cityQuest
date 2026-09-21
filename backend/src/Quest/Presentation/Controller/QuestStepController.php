<?php

declare(strict_types=1);

namespace App\Quest\Presentation\Controller;

use App\Quest\Domain\Repository\QuestStepRepositoryInterface;
use App\Shared\Authentication\Trait\AuthenticationTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

/**
 * Controller for quest steps endpoints
 */
#[Route('/api/quests', name: 'api_quests_')]
final class QuestStepController extends AbstractController
{
    use AuthenticationTrait;

    public function __construct(
        private readonly QuestStepRepositoryInterface $questStepRepository
    ) {
    }

    /**
     * Get a quest step (requires active quest)
     *
     * @Route("/{questId}/steps/{stepNumber}", name="step_get", methods=["GET"])
     */
    #[Route('/{questId}/steps/{stepNumber}', name: 'step_get', methods: ['GET'])]
    public function getStep(string $questId, int $stepNumber): JsonResponse
    {
        $user = $this->getAuthenticatedUserOr401Response();
        if ($user instanceof JsonResponse) {
            return $user;
        }

        try {
            $questUuid = Uuid::fromString($questId);
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'Invalid quest ID format'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $step = $this->questStepRepository->findByQuestAndNumber($questUuid, $stepNumber);
        if ($step === null || !$step->isActive()) {
            return new JsonResponse(
                ['error' => 'Step not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        return new JsonResponse([
            'data' => $step->toArray(),
        ]);
    }
}
