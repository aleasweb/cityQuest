<?php

declare(strict_types=1);

namespace App\User\Infrastructure\EventSubscriber;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Перехватывает успешную JWT аутентификацию и добавляет данные пользователя в JSON response. 
 * JWT в HttpOnly cookie (недоступен JavaScript).
 */
class JWTAuthenticationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::AUTHENTICATION_SUCCESS => 'onAuthenticationSuccess',
        ];
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();
        $data = $event->getData();

        // Add user data to response
        $data['user'] = [
            'id' => $user->getId()->toRfc4122(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        $event->setData($data);
    }
}

