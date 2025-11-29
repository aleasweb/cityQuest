<?php

declare(strict_types=1);

namespace App\Tests\UserProgress\Presentation\Controller;

use App\Quest\Domain\Entity\Quest;
use App\User\Domain\Entity\User;
use App\UserProgress\Domain\Entity\UserQuestProgress;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Uid\Uuid;

class UserProgressControllerTest extends WebTestCase
{
    private ?EntityManagerInterface $entityManager = null;

    protected function setUp(): void
    {
        // EntityManager будет инициализирован в каждом тесте отдельно
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if ($this->entityManager) {
            $this->entityManager->close();
        }
    }

    public function testGetUserProgressReturnsEmptyForNewUser(): void
    {
        $client = static::createClient();
        
        $user = $this->createTestUser('progress_test_user', $client);
        $token = $this->getJwtToken('progress_test_user', 'password123', $client);
        
        $client->request('GET', '/api/user/progress', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);
        
        $this->assertResponseIsSuccessful();
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('meta', $response);
        $this->assertEmpty($response['data']);
        $this->assertEquals(0, $response['meta']['total']);
    }

    public function testStartQuestCreatesProgress(): void
    {
        $client = static::createClient();
        
        $user = $this->createTestUser('start_quest_user', $client);
        $quest = $this->createTestQuest('Start Quest Test', $client);
        $token = $this->getJwtToken('start_quest_user', 'password123', $client);
        
        $client->request('POST', '/api/user/progress/' . $quest->getId() . '/start', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);
        
        $this->assertResponseStatusCodeSame(201);
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $response);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('active', $response['data']['status']);
    }

    public function testStartQuestReturnsConflictWhenActiveQuestExists(): void
    {
        $client = static::createClient();
        
        $user = $this->createTestUser('conflict_test_user', $client);
        $quest1 = $this->createTestQuest('Active Quest 1', $client);
        $quest2 = $this->createTestQuest('Active Quest 2', $client);
        $token = $this->getJwtToken('conflict_test_user', 'password123', $client);
        
        // Start first quest
        $client->request('POST', '/api/user/progress/' . $quest1->getId() . '/start', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);
        $this->assertResponseStatusCodeSame(201);
        
        // Try to start second quest - should fail with 409
        $client->request('POST', '/api/user/progress/' . $quest2->getId() . '/start', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);
        
        $this->assertResponseStatusCodeSame(409);
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $response);
        $this->assertStringContainsString('already has an active quest', $response['error']);
    }

    public function testPauseQuestChangesStatusToPaused(): void
    {
        $client = static::createClient();
        
        $user = $this->createTestUser('pause_test_user', $client);
        $quest = $this->createTestQuest('Pause Quest Test', $client);
        $token = $this->getJwtToken('pause_test_user', 'password123', $client);
        
        // Start quest first
        $client->request('POST', '/api/user/progress/' . $quest->getId() . '/start', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);
        $this->assertResponseStatusCodeSame(201);
        
        // Pause quest
        $client->request('PATCH', '/api/user/progress/' . $quest->getId() . '/pause', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);
        
        $this->assertResponseIsSuccessful();
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $response);
        $this->assertEquals('paused', $response['data']['status']);
    }

    public function testCompleteQuestChangesStatusToCompleted(): void
    {
        $client = static::createClient();
        
        $user = $this->createTestUser('complete_test_user', $client);
        $quest = $this->createTestQuest('Complete Quest Test', $client);
        $token = $this->getJwtToken('complete_test_user', 'password123', $client);
        
        // Start quest first
        $client->request('POST', '/api/user/progress/' . $quest->getId() . '/start', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);
        $this->assertResponseStatusCodeSame(201);
        
        // Complete quest
        $client->request('PATCH', '/api/user/progress/' . $quest->getId() . '/complete', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);
        
        $this->assertResponseIsSuccessful();
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $response);
        $this->assertEquals('completed', $response['data']['status']);
        $this->assertArrayHasKey('completedAt', $response['data']);
        $this->assertNotNull($response['data']['completedAt']);
    }

    public function testGetUserProgressWithStatusFilter(): void
    {
        $client = static::createClient();
        
        $user = $this->createTestUser('filter_test_user', $client);
        $quest1 = $this->createTestQuest('Active Quest', $client);
        $quest2 = $this->createTestQuest('Completed Quest', $client);
        $token = $this->getJwtToken('filter_test_user', 'password123', $client);
        
        // Start and complete first quest
        $client->request('POST', '/api/user/progress/' . $quest1->getId() . '/start', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);
        $client->request('PATCH', '/api/user/progress/' . $quest1->getId() . '/complete', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);
        
        // Start second quest (keep active)
        $client->request('POST', '/api/user/progress/' . $quest2->getId() . '/start', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);
        
        // Get only completed quests
        $client->request('GET', '/api/user/progress?status=completed', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);
        
        $this->assertResponseIsSuccessful();
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(1, $response['data']);
        $this->assertEquals('completed', $response['data'][0]['status']);
    }

    public function testProgressEndpointsRequireAuthentication(): void
    {
        $client = static::createClient();
        
        $quest = $this->createTestQuest('Auth Test Quest', $client);
        
        // Without token
        $client->request('GET', '/api/user/progress');
        $this->assertResponseStatusCodeSame(401);
        
        $client->request('POST', '/api/user/progress/' . $quest->getId() . '/start');
        $this->assertResponseStatusCodeSame(401);
        
        $client->request('PATCH', '/api/user/progress/' . $quest->getId() . '/pause');
        $this->assertResponseStatusCodeSame(401);
        
        $client->request('PATCH', '/api/user/progress/' . $quest->getId() . '/complete');
        $this->assertResponseStatusCodeSame(401);
    }

    private function getEntityManager($client = null): EntityManagerInterface
    {
        if (!$this->entityManager) {
            if ($client === null) {
                $kernel = self::bootKernel();
                $this->entityManager = $kernel->getContainer()
                    ->get('doctrine')
                    ->getManager();
            } else {
                $this->entityManager = $client->getContainer()
                    ->get('doctrine')
                    ->getManager();
            }
        }
        
        return $this->entityManager;
    }

    private function createTestUser(string $username, $client = null): User
    {
        $entityManager = $this->getEntityManager($client);
        
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($username . '@test.com');
        $user->setPassword(password_hash('password123', PASSWORD_BCRYPT));
        
        $entityManager->persist($user);
        $entityManager->flush();
        
        return $user;
    }

    private function createTestQuest(string $title, $client = null): Quest
    {
        $entityManager = $this->getEntityManager($client);
        
        $quest = new Quest($title);
        $quest->setDescription('Test quest description');
        $quest->setCity('Test City');
        $quest->setDifficulty('medium');
        
        $entityManager->persist($quest);
        $entityManager->flush();
        
        return $quest;
    }

    private function getJwtToken(string $username, string $password, $client): string
    {
        $client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'username' => $username,
            'password' => $password,
        ]));
        
        $response = json_decode($client->getResponse()->getContent(), true);
        
        return $response['token'];
    }
}
