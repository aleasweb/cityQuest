<?php

namespace App\Tests\HealthCheck\Presentation\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class HealthCheckControllerTest extends WebTestCase
{
    public function test__invoke()
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/health-check');

        $this->assertResponseIsSuccessful();

        $contentJson = $client->getResponse()->getContent();
        $expectedJson = '{"message":"work it"}';
        $this->assertJsonStringEqualsJsonString($expectedJson, $contentJson);

        $jsonResult = json_decode($contentJson, true);
        $this->assertEquals('work it', $jsonResult['message']);
    }
} 