<?php

declare(strict_types=1);

namespace App\Tests\Quest\Presentation\Controller;

use App\Tests\Helper\DatabaseTestTrait;
use App\Tests\Helper\TestAuthClient;
use App\Tests\Helper\TestObjectFactory;
use App\UserProgress\Domain\Entity\UserQuestProgress;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Uid\Uuid;

/**
 * Функциональные тесты для Like endpoint
 */
class QuestLikeControllerTest extends WebTestCase
{
    use DatabaseTestTrait;

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->closeEntityManager();
    }

    /**
     * Создает клиент и инициализирует БД для теста
     */
    private function createClientWithDatabase(array $tables = ['quests', 'users', 'user_quest_progress'])
    {
        $client = static::createClient();
        $this->setUpDatabase($client, $tables);
        return $client;
    }

    public function testToggleLikeRequiresAuthentication(): void
    {
        $client = $this->createClientWithDatabase();
        $em = $this->getEntityManager($client);
        
        // Создаем квест
        $quest = TestObjectFactory::createQuestWithDefaults($em, 'Test Quest');
        
        // Попытка лайкнуть без авторизации
        $client->request('POST', '/api/quests/' . $quest->getId() . '/like');
        
        $this->assertResponseStatusCodeSame(401);
    }

    public function testToggleLikeFailsForQuestNotInProgress(): void
    {
        $client = $this->createClientWithDatabase();
        $em = $this->getEntityManager($client);
        $passwordHasher = static::getContainer()->get('security.user_password_hasher');
        
        // Создаем пользователя
        $user = TestObjectFactory::createUserWithHasher($em, $passwordHasher, 'testuser');
        $token = TestAuthClient::getJwtToken($client, 'testuser');
        
        // Создаем квест
        $quest = TestObjectFactory::createQuestWithDefaults($em, 'Test Quest');
        
        // Пытаемся лайкнуть квест БЕЗ добавления в прогресс - должна быть ошибка
        $client->request(
            'POST',
            '/api/quests/' . $quest->getId() . '/like',
            [],
            [],
            TestAuthClient::createAuthHeaders($token)
        );
        
        $this->assertResponseStatusCodeSame(403);
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Quest must be in progress to be liked', $response['error']);
    }

    public function testToggleLikeSuccessfullyLikesQuest(): void
    {
        $client = $this->createClientWithDatabase();
        $em = $this->getEntityManager($client);
        $passwordHasher = static::getContainer()->get('security.user_password_hasher');
        
        // Создаем пользователя
        $user = TestObjectFactory::createUserWithHasher($em, $passwordHasher, 'testuser');
        $token = TestAuthClient::getJwtToken($client, 'testuser');
        
        // Создаем квест
        $quest = TestObjectFactory::createQuestWithDefaults($em, 'Test Quest');
        $initialLikesCount = $quest->getLikesCount();
        
        // Сначала начинаем квест
        $client->request(
            'POST',
            '/api/user/progress/' . $quest->getId() . '/start',
            [],
            [],
            TestAuthClient::createAuthHeaders($token)
        );
        $this->assertResponseStatusCodeSame(201);
        
        // Затем лайкаем квест
        $client->request(
            'POST',
            '/api/quests/' . $quest->getId() . '/like',
            [],
            [],
            TestAuthClient::createAuthHeaders($token)
        );
        
        $this->assertResponseIsSuccessful();
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $response);
        $this->assertEquals('Quest liked', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertTrue($response['data']['liked']);
        $this->assertEquals(1, $response['data']['likesCount']);
    }

    public function testToggleLikeUnlikesAlreadyLikedQuest(): void
    {
        $client = $this->createClientWithDatabase();
        $em = $this->getEntityManager($client);
        $passwordHasher = static::getContainer()->get('security.user_password_hasher');
        
        // Создаем пользователя
        $user = TestObjectFactory::createUserWithHasher($em, $passwordHasher, 'testuser');
        $token = TestAuthClient::getJwtToken($client, 'testuser');
        
        // Создаем квест
        $quest = TestObjectFactory::createQuestWithDefaults($em, 'Test Quest');
        
        // Начинаем квест
        $client->request(
            'POST',
            '/api/user/progress/' . $quest->getId() . '/start',
            [],
            [],
            TestAuthClient::createAuthHeaders($token)
        );
        $this->assertResponseStatusCodeSame(201);
        
        // Лайкаем квест
        $client->request(
            'POST',
            '/api/quests/' . $quest->getId() . '/like',
            [],
            [],
            TestAuthClient::createAuthHeaders($token)
        );
        $this->assertResponseIsSuccessful();
        
        // Затем убираем лайк
        $client->request(
            'POST',
            '/api/quests/' . $quest->getId() . '/like',
            [],
            [],
            TestAuthClient::createAuthHeaders($token)
        );
        
        $this->assertResponseIsSuccessful();
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Quest unliked', $response['message']);
        $this->assertFalse($response['data']['liked']);
        $this->assertEquals(0, $response['data']['likesCount']);
    }

    public function testToggleLikeReturnsNotFoundForNonExistentQuest(): void
    {
        $client = $this->createClientWithDatabase();
        $em = $this->getEntityManager($client);
        $passwordHasher = static::getContainer()->get('security.user_password_hasher');
        
        // Создаем пользователя
        $user = TestObjectFactory::createUserWithHasher($em, $passwordHasher, 'testuser');
        $token = TestAuthClient::getJwtToken($client, 'testuser');
        
        // Попытка лайкнуть несуществующий квест
        $nonExistentId = Uuid::v4();
        $client->request(
            'POST',
            '/api/quests/' . $nonExistentId . '/like',
            [],
            [],
            TestAuthClient::createAuthHeaders($token)
        );
        
        $this->assertResponseStatusCodeSame(404);
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Quest not found', $response['error']);
    }

    public function testGetQuestReturnsIsLikedByCurrentUserForAuthenticatedUser(): void
    {
        $client = $this->createClientWithDatabase();
        $em = $this->getEntityManager($client);
        $passwordHasher = static::getContainer()->get('security.user_password_hasher');
        
        // Создаем пользователя
        $user = TestObjectFactory::createUserWithHasher($em, $passwordHasher, 'testuser');
        $token = TestAuthClient::getJwtToken($client, 'testuser');
        
        // Создаем квест
        $quest = TestObjectFactory::createQuestWithDefaults($em, 'Test Quest');
        
        // Создаем progress (начинаем квест)
        $progress = new UserQuestProgress($user->getId(), $quest->getId());
        $progress->start();
        $em->persist($progress);
        $em->flush();
        
        // Лайкаем квест через API
        $client->request(
            'POST',
            '/api/quests/' . $quest->getId() . '/like',
            [],
            [],
            TestAuthClient::createAuthHeaders($token)
        );
        $this->assertResponseIsSuccessful();
        
        // Получаем квест
        $client->request(
            'GET',
            '/api/quests/' . $quest->getId(),
            [],
            [],
            TestAuthClient::createAuthHeaders($token)
        );
        
        $this->assertResponseIsSuccessful();
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $response);
        $this->assertTrue($response['data']['isLikedByCurrentUser']);
        $this->assertTrue($response['data']['isStartedByCurrentUser']);
    }

    public function testGetQuestReturnsIsLikedByCurrentUserFalseForUnauthenticatedUser(): void
    {
        $client = $this->createClientWithDatabase();
        $em = $this->getEntityManager($client);
        
        // Создаем квест
        $quest = TestObjectFactory::createQuestWithDefaults($em, 'Test Quest');
        
        // Получаем квест без авторизации
        $client->request('GET', '/api/quests/' . $quest->getId());
        
        $this->assertResponseIsSuccessful();
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $response);
        $this->assertFalse($response['data']['isLikedByCurrentUser']);
        $this->assertFalse($response['data']['isStartedByCurrentUser']);
    }

    public function testGetQuestReturnsIsStartedByCurrentUserTrueWhenQuestIsStarted(): void
    {
        $client = $this->createClientWithDatabase();
        $em = $this->getEntityManager($client);
        $passwordHasher = static::getContainer()->get('security.user_password_hasher');
        
        // Создаем пользователя
        $user = TestObjectFactory::createUserWithHasher($em, $passwordHasher, 'testuser');
        $token = TestAuthClient::getJwtToken($client, 'testuser');
        
        // Создаем квест
        $quest = TestObjectFactory::createQuestWithDefaults($em, 'Test Quest');
        
        // Создаем progress (начинаем квест, но не лайкаем)
        $progress = new UserQuestProgress($user->getId(), $quest->getId());
        $progress->start();
        $em->persist($progress);
        $em->flush();
        
        // Получаем квест
        $client->request(
            'GET',
            '/api/quests/' . $quest->getId(),
            [],
            [],
            TestAuthClient::createAuthHeaders($token)
        );
        
        $this->assertResponseIsSuccessful();
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $response);
        $this->assertTrue($response['data']['isStartedByCurrentUser']);
        $this->assertFalse($response['data']['isLikedByCurrentUser']);
    }

    public function testLikeIncreasesCountByOne(): void
    {
        $client = $this->createClientWithDatabase();
        $em = $this->getEntityManager($client);
        $passwordHasher = static::getContainer()->get('security.user_password_hasher');
        
        // Создаем пользователя и получаем токен
        $user = TestObjectFactory::createUserWithHasher($em, $passwordHasher, 'testuser');
        $token = TestAuthClient::getJwtToken($client, 'testuser');
        
        // Создаем квест с 0 лайков
        $quest = TestObjectFactory::createQuest(
            entityManager: $em,
            title: 'Test Quest for Like Count',
            description: 'Testing like count increment',
            city: 'Moscow',
            difficulty: 'easy',
            likesCount: 0
        );
        
        // Шаг 1: Получаем квест и сохраняем текущее количество лайков
        $client->request('GET', '/api/quests/' . $quest->getId());
        $this->assertResponseIsSuccessful();
        
        $initialResponse = json_decode($client->getResponse()->getContent(), true);
        $initialLikesCount = $initialResponse['data']['likesCount'];
        
        $this->assertEquals(0, $initialLikesCount, 'Initial likes count should be 0');
        
        // Шаг 2: Начинаем квест (обязательно перед лайком)
        $client->request(
            'POST',
            '/api/user/progress/' . $quest->getId() . '/start',
            [],
            [],
            TestAuthClient::createAuthHeaders($token)
        );
        $this->assertResponseStatusCodeSame(201);
        
        // Шаг 3: Делаем лайк
        $client->request(
            'POST',
            '/api/quests/' . $quest->getId() . '/like',
            [],
            [],
            TestAuthClient::createAuthHeaders($token)
        );
        $this->assertResponseIsSuccessful();
        
        // Шаг 4: Получаем квест снова и проверяем что количество увеличилось на 1
        $client->request('GET', '/api/quests/' . $quest->getId());
        $this->assertResponseIsSuccessful();
        
        $newResponse = json_decode($client->getResponse()->getContent(), true);
        $newLikesCount = $newResponse['data']['likesCount'];
        
        $this->assertEquals(
            $initialLikesCount + 1,
            $newLikesCount,
            'Likes count should increase by 1 after liking'
        );
        $this->assertEquals(1, $newLikesCount, 'New likes count should be exactly 1');
    }

    public function testToggleLikeWorksForPausedQuest(): void
    {
        $client = $this->createClientWithDatabase();
        $em = $this->getEntityManager($client);
        $passwordHasher = static::getContainer()->get('security.user_password_hasher');
        
        $user = TestObjectFactory::createUserWithHasher($em, $passwordHasher, 'testuser');
        $token = TestAuthClient::getJwtToken($client, 'testuser');
        
        $quest = TestObjectFactory::createQuest(
            entityManager: $em,
            title: 'Paused Quest Test',
            description: 'Test quest',
            city: 'Moscow',
            difficulty: 'easy',
            likesCount: 0
        );
        
        // Начинаем квест
        $client->request(
            'POST',
            '/api/user/progress/' . $quest->getId() . '/start',
            [],
            [],
            TestAuthClient::createAuthHeaders($token)
        );
        $this->assertResponseStatusCodeSame(201);
        
        // Ставим на паузу
        $client->request(
            'PATCH',
            '/api/user/progress/' . $quest->getId() . '/pause',
            [],
            [],
            TestAuthClient::createAuthHeaders($token)
        );
        $this->assertResponseIsSuccessful();
        
        // Лайкаем квест в статусе paused
        $client->request(
            'POST',
            '/api/quests/' . $quest->getId() . '/like',
            [],
            [],
            TestAuthClient::createAuthHeaders($token)
        );
        
        $this->assertResponseIsSuccessful();
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($response['data']['liked']);
        $this->assertEquals(1, $response['data']['likesCount']);
    }

    public function testToggleLikeWorksForCompletedQuest(): void
    {
        $client = $this->createClientWithDatabase();
        $em = $this->getEntityManager($client);
        $passwordHasher = static::getContainer()->get('security.user_password_hasher');
        
        $user = TestObjectFactory::createUserWithHasher($em, $passwordHasher, 'testuser');
        $token = TestAuthClient::getJwtToken($client, 'testuser');
        
        $quest = TestObjectFactory::createQuest(
            entityManager: $em,
            title: 'Completed Quest Test',
            description: 'Test quest',
            city: 'Moscow',
            difficulty: 'easy',
            likesCount: 0
        );
        
        // Начинаем квест
        $client->request(
            'POST',
            '/api/user/progress/' . $quest->getId() . '/start',
            [],
            [],
            TestAuthClient::createAuthHeaders($token)
        );
        $this->assertResponseStatusCodeSame(201);
        
        // Завершаем квест
        $client->request(
            'PATCH',
            '/api/user/progress/' . $quest->getId() . '/complete',
            [],
            [],
            TestAuthClient::createAuthHeaders($token)
        );
        $this->assertResponseIsSuccessful();
        
        // Лайкаем завершенный квест
        $client->request(
            'POST',
            '/api/quests/' . $quest->getId() . '/like',
            [],
            [],
            TestAuthClient::createAuthHeaders($token)
        );
        
        $this->assertResponseIsSuccessful();
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($response['data']['liked']);
        $this->assertEquals(1, $response['data']['likesCount']);
    }
}

