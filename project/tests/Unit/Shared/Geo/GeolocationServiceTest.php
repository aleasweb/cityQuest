<?php

declare(strict_types=1);

namespace App\Tests\Unit\Shared\Geo;

use App\Shared\Geo\Application\Service\GeolocationService;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for GeolocationService
 * Tests Haversine formula calculations and radius validation
 */
final class GeolocationServiceTest extends TestCase
{
    private GeolocationService $service;

    protected function setUp(): void
    {
        $this->service = new GeolocationService();
    }

    /**
     * Test: Calculate distance between two known points
     * Moscow Kremlin to Red Square (approx 500m)
     */
    public function testCalculateDistanceBetweenKnownPoints(): void
    {
        // Moscow Kremlin coordinates
        $lat1 = 55.752121;
        $lng1 = 37.617664;

        // Red Square coordinates
        $lat2 = 55.753544;
        $lng2 = 37.621211;

        $distance = $this->service->calculateDistance($lat1, $lng1, $lat2, $lng2);

        // Distance should be approximately 350-400 meters
        $this->assertGreaterThan(300, $distance);
        $this->assertLessThan(500, $distance);
    }

    /**
     * Test: Distance to the same point should be zero
     */
    public function testCalculateDistanceToSamePoint(): void
    {
        $lat = 53.20166;
        $lng = 45.00564;

        $distance = $this->service->calculateDistance($lat, $lng, $lat, $lng);

        $this->assertEquals(0.0, $distance, 'Distance to same point should be 0');
    }

    /**
     * Test: User within radius should return true
     */
    public function testIsWithinRadiusTrue(): void
    {
        $pointLat = 53.20166;
        $pointLng = 45.00564;

        // User 50 meters away (approximately)
        $userLat = 53.20166 + 0.0005;
        $userLng = 45.00564;

        $result = $this->service->isWithinRadius($userLat, $userLng, $pointLat, $pointLng, 100);

        $this->assertTrue($result, 'User should be within 100m radius');
    }

    /**
     * Test: User outside radius should return false
     */
    public function testIsWithinRadiusFalse(): void
    {
        $pointLat = 53.20166;
        $pointLng = 45.00564;

        // User 200 meters away (approximately)
        $userLat = 53.20166 + 0.002;
        $userLng = 45.00564;

        $result = $this->service->isWithinRadius($userLat, $userLng, $pointLat, $pointLng, 100);

        $this->assertFalse($result, 'User should be outside 100m radius');
    }

    /**
     * Test: User exactly at radius edge (boundary test)
     */
    public function testIsWithinRadiusBoundary(): void
    {
        $pointLat = 53.20166;
        $pointLng = 45.00564;

        // Calculate a point exactly 50m away
        $distance = 50.0; // meters
        $bearing = 0; // North
        $earthRadiusMeters = 6371000;
        
        $lat2 = $pointLat + ($distance / $earthRadiusMeters) * (180 / M_PI);
        $lng2 = $pointLng;

        $result = $this->service->isWithinRadius($lat2, $lng2, $pointLat, $pointLng, 50);

        $this->assertTrue($result, 'User at radius edge should be within radius');
    }
}
