<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Trait;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Трейт для проверки аутентификации в контроллерах.
 * Обеспечивает консистентный ответ при отсутствии JWT токена.
 */
trait AuthenticationTrait
{
    /**
     * Получает аутентифицированного пользователя или возвращает ошибку 401.
     * Fallback проверка - не должна срабатывать если Security firewall настроен корректно.
     *
     * @return UserInterface|JsonResponse Возвращает пользователя или JsonResponse с ошибкой 401
     */
    protected function getAuthenticatedUserOr401Response(): UserInterface|JsonResponse
    {
        $user = $this->getUser();
        
        if ($user === null) {
            return $this->json([
                'code' => 401,
                'message' => 'JWT Token not found'
            ], Response::HTTP_UNAUTHORIZED, ['WWW-Authenticate' => 'Bearer']);
        }

        return $user;
    }
}
