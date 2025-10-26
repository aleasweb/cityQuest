<?php

declare(strict_types=1);

namespace App\Tests\User\Presentation\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ProfileControllerTest extends WebTestCase
{
    private function createUserAndGetToken(
        $client,
        string $email,
        string $username,
        string $password = 'password123'
    ): string {
        // Register user
        $client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => $email,
            'password' => $password,
            'username' => $username,
        ]));

        // Login to get JWT token
        $client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'username' => $username,
            'password' => $password,
        ]));

        $data = json_decode($client->getResponse()->getContent(), true);

        return $data['token'];
    }

    // ========== GET OWN PROFILE TESTS ==========

    public function testGetProfileReturnsUserData(): void
    {
        $client = static::createClient();
        $token = $this->createUserAndGetToken($client, 'profile1@example.com', 'profile1user');

        $client->request('GET', '/api/user/profile', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertArrayHasKey('username', $data);
        $this->assertArrayHasKey('createdAt', $data);
        $this->assertEquals('profile1@example.com', $data['email']);
        $this->assertEquals('profile1user', $data['username']);
    }

    public function testGetProfileRequiresAuthentication(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/user/profile');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    // ========== GET PUBLIC PROFILE TESTS ==========

    public function testGetPublicProfileReturnsPublicData(): void
    {
        $client = static::createClient();
        // Create user first
        $this->createUserAndGetToken($client, 'public1@example.com', 'public1user');

        $client->request('GET', '/api/users/public1user');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('username', $data);
        $this->assertArrayHasKey('createdAt', $data);
        $this->assertArrayNotHasKey('email', $data); // Email не должен быть в публичном профиле
        $this->assertEquals('public1user', $data['username']);
    }

    public function testGetPublicProfileDoesNotRequireAuthentication(): void
    {
        $client = static::createClient();
        // Create user first
        $this->createUserAndGetToken($client, 'nojwt1@example.com', 'nojwt1user');

        // Запрос БЕЗ JWT токена
        $client->request('GET', '/api/users/nojwt1user');

        // Должен вернуть 200, а не 401
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('nojwt1user', $data['username']);
    }

    public function testGetPublicProfileReturnsNotFoundForNonExistentUser(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/users/nonexistentuser999');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('nonexistentuser999', $data['error']);
    }

    // ========== UPDATE PROFILE TESTS ==========

    public function testUpdateProfileChangesEmail(): void
    {
        $client = static::createClient();
        $token = $this->createUserAndGetToken($client, 'oldmail1@example.com', 'update1user');

        $client->request('PATCH', '/api/user/profile', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'newmail1@example.com',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('user', $data);
        $this->assertEquals('newmail1@example.com', $data['user']['email']);
        $this->assertEquals('update1user', $data['user']['username']);
    }

    public function testUpdateProfileWithDuplicateEmail(): void
    {
        $client = static::createClient();
        
        // Create first user
        $this->createUserAndGetToken($client, 'first1@example.com', 'first1user');

        // Create second user
        $token = $this->createUserAndGetToken($client, 'second1@example.com', 'second1user');

        // Try to update second user's email to first user's email
        $client->request('PATCH', '/api/user/profile', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'first1@example.com',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('first1@example.com', $data['error']);
    }

    public function testUpdateProfileWithInvalidEmail(): void
    {
        $client = static::createClient();
        $token = $this->createUserAndGetToken($client, 'valid1@example.com', 'valid1user');

        $client->request('PATCH', '/api/user/profile', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'invalid-email-format',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('violations', $data);
    }

    public function testUpdateProfileRequiresAuthentication(): void
    {
        $client = static::createClient();

        $client->request('PATCH', '/api/user/profile', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'newemail@example.com',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
