<?php

declare(strict_types=1);

namespace App\Tests\UserProgress\Presentation\Controller;

use App\Tests\Helper\TestAuthClient;
use App\Tests\Helper\DatabaseTestTrait;
use App\Tests\Helper\TestObjectFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StartQuestBugTest extends WebTestCase
{
    use DatabaseTestTrait;

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->closeEntityManager();
    }

    public function testStartQuestAfterAbandon(): void
    {
        $client = static::createClient();
        
        $user = TestObjectFactory::createUser($this->getEntityManager($client), 'bug_test_user');
        $quest = TestObjectFactory::createQuest(
            $this->getEntityManager($client), 
            'Bug Quest Test'
        );
        $token = TestAuthClient::getJwtToken($client, $user->getUsername());

        // Start quest
        $client->request('POST', '/api/user/progress/' . $quest->getId() . '/start', [], [], 
            TestAuthClient::createAuthHeaders($token)
        );
        $this->assertEquals(201, $client->getResponse()->getStatusCode());

        // Abandon quest
        $client->request('DELETE', '/api/user/progress/' . $quest->getId(), [], [], 
            TestAuthClient::createAuthHeaders($token)
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Start quest again
        $client->request('POST', '/api/user/progress/' . $quest->getId() . '/start', [], [], 
            TestAuthClient::createAuthHeaders($token)
        );
        $this->assertEquals(201, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());
    }
}
