<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * Helper для работы с JWT аутентификацией в тестах.
 */
class TestAuthClient
{
    /**
     * Получает JWT токен для пользователя через API login endpoint.
     *
     * @param KernelBrowser $client
     * @param string $username
     * @param string $password
     * @return string JWT токен
     * @throws \RuntimeException если не удалось получить токен
     */
    public static function getJwtToken(
        KernelBrowser $client,
        string $username,
        string $password = 'password123'
    ): string {
        $client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'username' => $username,
            'password' => $password,
        ]));

        $response = json_decode($client->getResponse()->getContent(), true);

        if (!isset($response['token'])) {
            throw new \RuntimeException(
                'Failed to get JWT token. Response: ' . json_encode($response)
            );
        }

        return $response['token'];
    }

    /**
     * Создает заголовки для авторизованного запроса.
     *
     * @param string $token JWT токен
     * @param array<string, string> $additionalHeaders Дополнительные заголовки
     * @return array<string, string>
     */
    public static function createAuthHeaders(
        string $token,
        array $additionalHeaders = []
    ): array {
        return array_merge([
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ], $additionalHeaders);
    }
}

