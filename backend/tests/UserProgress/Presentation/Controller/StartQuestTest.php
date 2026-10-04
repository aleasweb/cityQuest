<?php

declare(strict_types=1);

namespace App\Tests\UserProgress\Presentation\Controller;

use App\Tests\Helper\TestAuthClient;
use App\Tests\Helper\DatabaseTestTrait;
use App\Tests\Helper\TestObjectFactory;
use App\UserProgress\Domain\ValueObject\QuestStatus;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StartQuestTest extends WebTestCase
{
    use DatabaseTestTrait;

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->closeEntityManager();
    }

    public function testStartQuestWhenNoActiveQuests(): void
    {
        $client = static::createClient();
        
        $user = TestObjectFactory::createUser($this->getEntityManager($client), 'start_quest_user_1');
        $quest = TestObjectFactory::createQuest(
            $this->getEntityManager($client), 
            'Start Quest Test 1'
        );
        $token = TestAuthClient::getJwtToken($client, $user->getUsername());

        $client->request('POST', '/api/user/progress/' . $quest->getId() . '/start', [], [], 
            TestAuthClient::createAuthHeaders($token)
        );

        $response = $client->getResponse();
        $this->assertEquals(201, $response->getStatusCode(), $response->getContent());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Quest started successfully', $data['message']);
        $this->assertEquals(QuestStatus::ACTIVE->value, $data['data']['status']);
    }

    public function testStartQuestWhenActiveQuestExists(): void
    {
        $client = static::createClient();
        
        $user = TestObjectFactory::createUser($this->getEntityManager($client), 'start_quest_user_2');
        $quest1 = TestObjectFactory::createQuest(
            $this->getEntityManager($client), 
            'Start Quest Test 2'
        );
        $quest2 = TestObjectFactory::createQuest(
            $this->getEntityManager($client), 
            'Start Quest Test 3'
        );
        $token = TestAuthClient::getJwtToken($client, $user->getUsername());

        // Start first quest
        $client->request('POST', '/api/user/progress/' . $quest1->getId() . '/start', [], [], 
            TestAuthClient::createAuthHeaders($token)
        );
        $this->assertEquals(201, $client->getResponse()->getStatusCode());

        // Try to start second quest
        $client->request('POST', '/api/user/progress/' . $quest2->getId() . '/start', [], [], 
            TestAuthClient::createAuthHeaders($token)
        );

        $response = $client->getResponse();
        $this->assertEquals(409, $response->getStatusCode(), $response->getContent());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('User already has an active quest. Pause it before starting a new one.', $data['error']);
    }

    public function testStartQuestWithoutActiveStepsReturns422(): void
    {
        $client = static::createClient();

        $user = TestObjectFactory::createUser($this->getEntityManager($client), 'start_quest_no_steps');
        $quest = TestObjectFactory::createQuest(
            $this->getEntityManager($client),
            'Quest Without Steps',
            withDefaultStep: false
        );
        $token = TestAuthClient::getJwtToken($client, $user->getUsername());

        $client->request(
            'POST',
            '/api/user/progress/' . $quest->getId() . '/start',
            [],
            [],
            TestAuthClient::createAuthHeaders($token)
        );

        $response = $client->getResponse();
        $this->assertEquals(422, $response->getStatusCode(), $response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertStringContainsString('has no active steps', $data['error']);
    }
}
