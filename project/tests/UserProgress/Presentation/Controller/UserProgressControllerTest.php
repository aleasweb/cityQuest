<?php

declare(strict_types=1);

namespace App\Tests\UserProgress\Presentation\Controller;

use App\Tests\Helper\TestAuthClient;
use App\Tests\Helper\DatabaseTestTrait;
use App\Tests\Helper\TestObjectFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserProgressControllerTest extends WebTestCase
{
    use DatabaseTestTrait;

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->closeEntityManager();
    }

    public function testGetUserProgressReturnsEmptyForNewUser(): void
    {
        $client = static::createClient();
        
        $user = TestObjectFactory::createUser($this->getEntityManager($client), 'progress_test_user');
        $token = TestAuthClient::getJwtToken($client, 'progress_test_user');
        
        $client->request('GET', '/api/user/progress', [], [], 
            TestAuthClient::createAuthHeaders($token)
        );
        
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
        
        $user = TestObjectFactory::createUser($this->getEntityManager($client), 'start_quest_user');
        $quest = TestObjectFactory::createQuest(
            $this->getEntityManager($client), 
            'Start Quest Test',
            description: 'Test quest description',
            city: 'Test City',
            difficulty: 'medium'
        );
        $token = TestAuthClient::getJwtToken($client, 'start_quest_user');
        
        $client->request('POST', '/api/user/progress/' . $quest->getId() . '/start', [], [], 
            TestAuthClient::createAuthHeaders($token)
        );
        
        $this->assertResponseStatusCodeSame(201);
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $response);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('active', $response['data']['status']);
    }

    public function testStartQuestReturnsConflictWhenActiveQuestExists(): void
    {
        $client = static::createClient();
        
        $user = TestObjectFactory::createUser($this->getEntityManager($client), 'conflict_test_user');
        $quest1 = TestObjectFactory::createQuest(
            $this->getEntityManager($client), 
            'Active Quest 1',
            description: 'Test quest description',
            city: 'Test City',
            difficulty: 'medium'
        );
        $quest2 = TestObjectFactory::createQuest(
            $this->getEntityManager($client), 
            'Active Quest 2',
            description: 'Test quest description',
            city: 'Test City',
            difficulty: 'medium'
        );
        $token = TestAuthClient::getJwtToken($client, 'conflict_test_user');
        
        // Start first quest
        $client->request('POST', '/api/user/progress/' . $quest1->getId() . '/start', [], [], 
            TestAuthClient::createAuthHeaders($token)
        );
        $this->assertResponseStatusCodeSame(201);
        
        // Try to start second quest - should fail with 409
        $client->request('POST', '/api/user/progress/' . $quest2->getId() . '/start', [], [], 
            TestAuthClient::createAuthHeaders($token)
        );
        
        $this->assertResponseStatusCodeSame(409);
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $response);
        $this->assertStringContainsString('already has an active quest', $response['error']);
    }

    public function testPauseQuestChangesStatusToPaused(): void
    {
        $client = static::createClient();
        
        $user = TestObjectFactory::createUser($this->getEntityManager($client), 'pause_test_user');
        $quest = TestObjectFactory::createQuest(
            $this->getEntityManager($client), 
            'Pause Quest Test',
            description: 'Test quest description',
            city: 'Test City',
            difficulty: 'medium'
        );
        $token = TestAuthClient::getJwtToken($client, 'pause_test_user');
        
        // Start quest first
        $client->request('POST', '/api/user/progress/' . $quest->getId() . '/start', [], [], 
            TestAuthClient::createAuthHeaders($token)
        );
        $this->assertResponseStatusCodeSame(201);
        
        // Pause quest
        $client->request('PATCH', '/api/user/progress/' . $quest->getId() . '/pause', [], [], 
            TestAuthClient::createAuthHeaders($token)
        );
        
        $this->assertResponseIsSuccessful();
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $response);
        $this->assertEquals('paused', $response['data']['status']);
    }

    public function testCompleteQuestChangesStatusToCompleted(): void
    {
        $client = static::createClient();
        
        $user = TestObjectFactory::createUser($this->getEntityManager($client), 'complete_test_user');
        $quest = TestObjectFactory::createQuest(
            $this->getEntityManager($client), 
            'Complete Quest Test',
            description: 'Test quest description',
            city: 'Test City',
            difficulty: 'medium'
        );
        $token = TestAuthClient::getJwtToken($client, 'complete_test_user');
        
        // Start quest first
        $client->request('POST', '/api/user/progress/' . $quest->getId() . '/start', [], [], 
            TestAuthClient::createAuthHeaders($token)
        );
        $this->assertResponseStatusCodeSame(201);
        
        // Complete quest
        $client->request('PATCH', '/api/user/progress/' . $quest->getId() . '/complete', [], [], 
            TestAuthClient::createAuthHeaders($token)
        );
        
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
        
        $user = TestObjectFactory::createUser($this->getEntityManager($client), 'filter_test_user');
        $quest1 = TestObjectFactory::createQuest(
            $this->getEntityManager($client), 
            'Active Quest',
            description: 'Test quest description',
            city: 'Test City',
            difficulty: 'medium'
        );
        $quest2 = TestObjectFactory::createQuest(
            $this->getEntityManager($client), 
            'Completed Quest',
            description: 'Test quest description',
            city: 'Test City',
            difficulty: 'medium'
        );
        $token = TestAuthClient::getJwtToken($client, 'filter_test_user');
        
        // Start and complete first quest
        $client->request('POST', '/api/user/progress/' . $quest1->getId() . '/start', [], [], 
            TestAuthClient::createAuthHeaders($token)
        );
        $client->request('PATCH', '/api/user/progress/' . $quest1->getId() . '/complete', [], [], 
            TestAuthClient::createAuthHeaders($token)
        );
        
        // Start second quest (keep active)
        $client->request('POST', '/api/user/progress/' . $quest2->getId() . '/start', [], [], 
            TestAuthClient::createAuthHeaders($token)
        );
        
        // Get only completed quests
        $client->request('GET', '/api/user/progress?status=completed', [], [], 
            TestAuthClient::createAuthHeaders($token)
        );
        
        $this->assertResponseIsSuccessful();
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(1, $response['data']);
        $this->assertEquals('completed', $response['data'][0]['status']);
    }

    public function testProgressEndpointsRequireAuthentication(): void
    {
        $client = static::createClient();
        
        $quest = TestObjectFactory::createQuest(
            $this->getEntityManager($client), 
            'Auth Test Quest',
            description: 'Test quest description',
            city: 'Test City',
            difficulty: 'medium'
        );
        
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

    public function testGetUserProgressReturnsIsLikedStatus(): void
    {
        $client = static::createClient();
        
        $passwordHasher = static::getContainer()->get('security.user_password_hasher');
        $user = TestObjectFactory::createUserWithHasher(
            $this->getEntityManager($client),
            $passwordHasher,
            'liked_test_user'
        );
        
        $quest1 = TestObjectFactory::createQuest(
            $this->getEntityManager($client), 
            'Liked Quest',
            description: 'This quest is liked',
            city: 'Test City',
            difficulty: 'medium',
            likesCount: 0
        );
        
        $quest2 = TestObjectFactory::createQuest(
            $this->getEntityManager($client), 
            'Not Liked Quest',
            description: 'This quest is not liked',
            city: 'Test City',
            difficulty: 'easy',
            likesCount: 0
        );
        
        $token = TestAuthClient::getJwtToken($client, 'liked_test_user');
        
        // Start both quests
        $client->request('POST', '/api/user/progress/' . $quest1->getId() . '/start', [], [], 
            TestAuthClient::createAuthHeaders($token)
        );
        $this->assertResponseStatusCodeSame(201);
        
        // Complete first quest to allow starting second
        $client->request('PATCH', '/api/user/progress/' . $quest1->getId() . '/complete', [], [], 
            TestAuthClient::createAuthHeaders($token)
        );
        
        $client->request('POST', '/api/user/progress/' . $quest2->getId() . '/start', [], [], 
            TestAuthClient::createAuthHeaders($token)
        );
        $this->assertResponseStatusCodeSame(201);
        
        // Like only first quest
        $client->request('POST', '/api/quests/' . $quest1->getId() . '/like', [], [], 
            TestAuthClient::createAuthHeaders($token)
        );
        $this->assertResponseIsSuccessful();
        
        // Get user progress
        $client->request('GET', '/api/user/progress', [], [], 
            TestAuthClient::createAuthHeaders($token)
        );
        
        $this->assertResponseIsSuccessful();
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $response);
        $this->assertCount(2, $response['data']);
        
        // Find quest1 and quest2 in response
        $quest1Data = null;
        $quest2Data = null;
        foreach ($response['data'] as $progressItem) {
            if ($progressItem['questId'] === (string) $quest1->getId()) {
                $quest1Data = $progressItem;
            }
            if ($progressItem['questId'] === (string) $quest2->getId()) {
                $quest2Data = $progressItem;
            }
        }
        
        $this->assertNotNull($quest1Data, 'Quest 1 should be in progress data');
        $this->assertNotNull($quest2Data, 'Quest 2 should be in progress data');
        
        // Verify isLiked field exists and is correct
        $this->assertArrayHasKey('isLiked', $quest1Data);
        $this->assertArrayHasKey('isLiked', $quest2Data);
        
        $this->assertTrue($quest1Data['isLiked'], 'Quest 1 should be liked');
        $this->assertFalse($quest2Data['isLiked'], 'Quest 2 should not be liked');
        
        // Verify meta.liked contains total liked quests count
        $this->assertArrayHasKey('meta', $response);
        $this->assertArrayHasKey('liked', $response['meta']);
        $this->assertEquals(1, $response['meta']['liked'], 'User should have 1 liked quest in total');
    }
}
