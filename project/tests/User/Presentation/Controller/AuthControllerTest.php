<?php

declare(strict_types=1);

namespace App\Tests\User\Presentation\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AuthControllerTest extends WebTestCase
{
    // ========== REGISTRATION TESTS ==========

    public function testSuccessfulRegistration(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'username' => 'newuser',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        
        $data = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('user', $data);
        $this->assertArrayHasKey('id', $data['user']);
        $this->assertArrayHasKey('email', $data['user']);
        $this->assertArrayHasKey('username', $data['user']);
        $this->assertArrayHasKey('createdAt', $data['user']);
        
        $this->assertEquals('newuser@example.com', $data['user']['email']);
        $this->assertEquals('newuser', $data['user']['username']);
    }

    public function testRegistrationWithExistingEmail(): void
    {
        $client = static::createClient();

        // First registration
        $client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'duplicate@example.com',
            'password' => 'password123',
            'username' => 'user1',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        // Second registration with same email
        $client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'duplicate@example.com',
            'password' => 'password456',
            'username' => 'user2',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('duplicate@example.com', $data['error']);
    }

    public function testRegistrationWithExistingUsername(): void
    {
        $client = static::createClient();

        // First registration
        $client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'user1@example.com',
            'password' => 'password123',
            'username' => 'duplicateuser',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        // Second registration with same username
        $client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'user2@example.com',
            'password' => 'password456',
            'username' => 'duplicateuser',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('duplicateuser', $data['error']);
    }

    public function testRegistrationWithInvalidEmail(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'invalid-email',
            'password' => 'password123',
            'username' => 'testuser',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertArrayHasKey('violations', $data);
        $this->assertArrayHasKey('email', $data['violations']);
    }

    public function testRegistrationWithShortPassword(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'user@example.com',
            'password' => '123',
            'username' => 'testuser',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('violations', $data);
        $this->assertArrayHasKey('password', $data['violations']);
        $this->assertStringContainsString('8 characters', $data['violations']['password']);
    }

    public function testRegistrationWithShortUsername(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'user@example.com',
            'password' => 'password123',
            'username' => 'ab',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('violations', $data);
        $this->assertArrayHasKey('username', $data['violations']);
        $this->assertStringContainsString('3 characters', $data['violations']['username']);
    }

    public function testRegistrationWithInvalidUsernameCharacters(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'user@example.com',
            'password' => 'password123',
            'username' => 'user@name',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('violations', $data);
        $this->assertArrayHasKey('username', $data['violations']);
    }

    public function testRegistrationWithMissingFields(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => '',
            'password' => '',
            'username' => '',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('violations', $data);
        $this->assertGreaterThanOrEqual(2, count($data['violations']));
    }

    // ========== LOGIN TESTS ==========

    public function testSuccessfulLogin(): void
    {
        $client = static::createClient();

        // Register user first
        $client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'logintest@example.com',
            'password' => 'password123',
            'username' => 'loginuser',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        // Now login
        $client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'logintest@example.com',
            'password' => 'password123',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $data);
        $this->assertNotEmpty($data['token']);
        
        // Verify JWT format (3 parts separated by dots)
        $tokenParts = explode('.', $data['token']);
        $this->assertCount(3, $tokenParts);
    }

    public function testLoginWithInvalidEmail(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testLoginWithInvalidPassword(): void
    {
        $client = static::createClient();

        // Register user first
        $client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'pwdtest@example.com',
            'password' => 'correctpassword',
            'username' => 'pwduser',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        // Try login with wrong password
        $client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'pwdtest@example.com',
            'password' => 'wrongpassword',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testLoginWithNonExistentUser(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'doesnotexist@example.com',
            'password' => 'password123',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    // ========== LOGOUT TEST ==========

    public function testLogout(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/auth/logout');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
    }
}
