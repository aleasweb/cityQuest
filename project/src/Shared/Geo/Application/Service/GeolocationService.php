<?php

declare(strict_types=1);

namespace App\Shared\Geo\Application\Service;

final class GeolocationService
{
    private const EARTH_RADIUS_KM = 6371;
    private const EARTH_RADIUS_METERS = 6371000;

    /**
     * Calculate distance between two points using Haversine formula
     */
    public function calculateDistance(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2
    ): float {
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $lng1Rad = deg2rad($lng1);
        $lng2Rad = deg2rad($lng2);

        $deltaLat = $lat2Rad - $lat1Rad;
        $deltaLng = $lng2Rad - $lng1Rad;

        // Haversine formula
        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
            cos($lat1Rad) * cos($lat2Rad) *
            sin($deltaLng / 2) * sin($deltaLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_METERS * $c;
    }

    /**
     * Check if user coordinates are within the specified radius from a point
     */
    public function isWithinRadius(
        float $userLat,
        float $userLng,
        float $pointLat,
        float $pointLng,
        int $radiusMeters
    ): bool {
        $distance = $this->calculateDistance($userLat, $userLng, $pointLat, $pointLng);
        return $distance <= $radiusMeters;
    }
}
