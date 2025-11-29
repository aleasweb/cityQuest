<?php

declare(strict_types=1);

namespace App\Tests\Quest\Presentation\Controller;

use App\Tests\Helper\DatabaseTestTrait;
use App\Tests\Helper\TestObjectFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Uid\Uuid;

class QuestControllerTest extends WebTestCase
{
    use DatabaseTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        // Очищаем таблицы перед каждым тестом
        $this->cleanupDatabase();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->closeEntityManager();
    }

    public function testGetQuestReturnsQuestData(): void
    {
        $client = static::createClient();
        
        // Создаем тестовый квест
        $quest = TestObjectFactory::createQuestWithDefaults($this->getEntityManager(), 'Integration Test Quest');
        
        $client->request('GET', '/api/quests/' . (string) $quest->getId());
        
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $data = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertIsArray($data);
        $this->assertEquals((string) $quest->getId(), $data['id']);
        $this->assertEquals('Integration Test Quest', $data['title']);
        $this->assertEquals('Test description for integration', $data['description']);
        $this->assertEquals('Moscow', $data['city']);
        $this->assertEquals('medium', $data['difficulty']);
        $this->assertEquals(90, $data['durationMinutes']);
        $this->assertEquals(3.2, $data['distanceKm']);
        $this->assertEquals('Integration Author', $data['author']);
        $this->assertEquals(15, $data['likesCount']);
        $this->assertTrue($data['isPopular']);
        $this->assertArrayHasKey('createdAt', $data);
        $this->assertArrayHasKey('updatedAt', $data);
    }

    public function testGetQuestReturnsNotFoundForNonExistentQuest(): void
    {
        $client = static::createClient();
        
        $nonExistentId = Uuid::v4();
        $client->request('GET', '/api/quests/' . (string) $nonExistentId);
        
        $this->assertResponseStatusCodeSame(404);
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('Quest with id', $data['error']);
        $this->assertStringContainsString('not found', $data['error']);
    }

    public function testGetQuestReturnsBadRequestForInvalidUuid(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/quests/invalid-uuid-format');
        
        $this->assertResponseStatusCodeSame(400);
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Invalid quest ID format', $data['error']);
    }

    public function testGetQuestDoesNotRequireAuthentication(): void
    {
        $client = static::createClient();
        
        // Создаем тестовый квест
        $quest = TestObjectFactory::createQuest($this->getEntityManager(), 'Public Access Test Quest');
        
        // Запрос без JWT токена
        $client->request('GET', '/api/quests/' . (string) $quest->getId());
        
        // Должен вернуть 200, а не 401
        $this->assertResponseIsSuccessful();
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Public Access Test Quest', $data['title']);
    }

    public function testGetQuestsReturnsListOfQuests(): void
    {
        $client = static::createClient();
        
        // Создаем несколько тестовых квестов
        $quest1 = TestObjectFactory::createQuest($this->getEntityManager(), 'Quest List Test 1');
        $quest2 = TestObjectFactory::createQuest($this->getEntityManager(), 'Quest List Test 2');
        
        $client->request('GET', '/api/quests');
        
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $response = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('meta', $response);
        $this->assertIsArray($response['data']);
        $this->assertGreaterThanOrEqual(2, count($response['data']));
        $this->assertArrayHasKey('total', $response['meta']);
        $this->assertArrayHasKey('limit', $response['meta']);
        $this->assertArrayHasKey('offset', $response['meta']);
    }

    public function testGetQuestsWithCityFilter(): void
    {
        $client = static::createClient();
        
        $quest = TestObjectFactory::createQuest($this->getEntityManager(), 'Moscow Quest');
        
        $client->request('GET', '/api/quests?city=Moscow');
        
        $this->assertResponseIsSuccessful();
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $response);
        
        // Check that all returned quests are from Moscow
        foreach ($response['data'] as $questData) {
            $this->assertEquals('Moscow', $questData['city']);
        }
    }

    public function testGetQuestsWithPagination(): void
    {
        $client = static::createClient();
        
        // Create multiple quests
        for ($i = 1; $i <= 3; $i++) {
            TestObjectFactory::createQuest($this->getEntityManager(), "Pagination Test Quest $i");
        }
        
        $client->request('GET', '/api/quests?limit=2&offset=0');
        
        $this->assertResponseIsSuccessful();
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('meta', $response);
        $this->assertEquals(2, $response['meta']['limit']);
        $this->assertEquals(0, $response['meta']['offset']);
        $this->assertLessThanOrEqual(2, count($response['data']));
    }

    public function testGetNearbyQuestsRequiresLatLng(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/quests/nearby');
        
        $this->assertResponseStatusCodeSame(400);
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $response);
        $this->assertStringContainsString('Latitude', $response['error']);
    }

    public function testGetNearbyQuestsReturnsQuests(): void
    {
        $client = static::createClient();
        
        // Create quest with coordinates (Moscow center)
        $quest1 = TestObjectFactory::createQuest(
            entityManager: $this->getEntityManager(),
            title: 'Moscow Nearby Quest',
            latitude: 55.7558,
            longitude: 37.6173
        );
        $quest2 = TestObjectFactory::createQuest(
            entityManager: $this->getEntityManager(),
            title: 'London Quest',
            latitude: 51.5002,
            longitude: 0.0000
        );
        
        // Search nearby (very close to the quest)
        $client->request('GET', '/api/quests/nearby?lat=55.7558&lng=37.6173&radius=10');
        
        $this->assertResponseIsSuccessful();
        
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('meta', $response);
        $this->assertEquals(55.7558, $response['meta']['latitude']);
        $this->assertEquals(37.6173, $response['meta']['longitude']);
        $this->assertEquals(10, $response['meta']['radius']);
        
        // Проверяем, что вернулся корректный квест (московский)
        $this->assertCount(1, $response['data'], 'Должен вернуться только один квест в радиусе');
        
        $returnedQuest = $response['data'][0];
        $this->assertEquals((string) $quest1->getId(), $returnedQuest['id']);
        $this->assertEquals('Moscow Nearby Quest', $returnedQuest['title']);
        $this->assertEquals(55.7558, $returnedQuest['latitude']);
        $this->assertEquals(37.6173, $returnedQuest['longitude']);
        
        // Проверяем, что лондонский квест НЕ вернулся
        $questIds = array_column($response['data'], 'id');
        $this->assertNotContains((string) $quest2->getId(), $questIds, 'Лондонский квест не должен попасть в результаты поиска по Москве');
    }
}
