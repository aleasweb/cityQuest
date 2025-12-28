<?php

namespace App\Platform\Application\Service;

use App\Platform\ValueObject\Platform;
use Symfony\Component\HttpFoundation\RequestStack;

class PlatformResolver
{
    private const NATIVE_APP_HEADER = 'X-App-Platform';

    public function __construct(
        private readonly RequestStack $requestStack
    ) {
    }

    public function resolve(): Platform
    {
        $currentRequest = $this->requestStack->getCurrentRequest();

        if ($currentRequest === null) {
            return Platform::unknown();
        }

        $userAgent = $currentRequest->headers->get('User-Agent', '');
        $appPlatform = $currentRequest->headers->get(self::NATIVE_APP_HEADER);

        // Приоритет: кастомный header для нативных приложений
        if ($appPlatform !== null) {
            return $this->resolveNativeApp($appPlatform, $userAgent);
        }

        // Определение мобильного браузера
        if ($this->isMobileBrowser($userAgent)) {
            return $this->resolveMobileBrowser($userAgent);
        }

        // Десктопный браузер
        return Platform::web(
            browser: $this->detectBrowser($userAgent),
            browserVersion: $this->detectBrowserVersion($userAgent),
            os: $this->detectOS($userAgent)
        );
    }

    private function isMobileBrowser(string $userAgent): bool
    {
        return str_contains($userAgent, 'Mobile')
            || str_contains($userAgent, 'Android')
            || (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad'));
    }

    private function resolveMobileBrowser(string $userAgent): Platform
    {
        return Platform::web(
            browser: $this->detectBrowser($userAgent) . ' Mobile',
            browserVersion: $this->detectBrowserVersion($userAgent),
            os: $this->detectMobileOS($userAgent)
        );
    }

    private function resolveNativeApp(string $appPlatform, string $userAgent): Platform
    {
        $parts = explode('/', $appPlatform); // Формат: "ios/1.2.3" или "android/1.2.3"
        $type = strtolower($parts[0] ?? 'unknown');
        $appVersion = $parts[1] ?? 'unknown';

        if ($type === 'ios') {
            return Platform::ios(
                appVersion: $appVersion,
                osVersion: $this->extractIOSVersion($userAgent),
                deviceModel: $this->extractIOSDevice($userAgent)
            );
        }

        if ($type === 'android') {
            return Platform::android(
                appVersion: $appVersion,
                osVersion: $this->extractAndroidVersion($userAgent),
                deviceModel: $this->extractAndroidDevice($userAgent)
            );
        }

        return Platform::unknown();
    }

    private function detectBrowser(string $userAgent): string
    {
        // Порядок важен! Edge, Opera содержат Chrome; Chrome содержит Safari
        if (str_contains($userAgent, 'Edg')) {
            return 'Edge';
        }
        if (str_contains($userAgent, 'OPR') || str_contains($userAgent, 'Opera')) {
            return 'Opera';
        }
        if (str_contains($userAgent, 'Chrome')) {
            return 'Chrome';
        }
        if (str_contains($userAgent, 'Firefox')) {
            return 'Firefox';
        }
        if (str_contains($userAgent, 'Safari')) {
            return 'Safari';
        }

        return 'Unknown';
    }

    private function detectBrowserVersion(string $userAgent): string
    {
        // Chrome/120.0.6099.129
        if (preg_match('/Chrome\/(\d+\.\d+\.\d+\.\d+)/', $userAgent, $matches)) {
            return $matches[1];
        }
        // Firefox/120.0
        if (preg_match('/Firefox\/(\d+\.\d+)/', $userAgent, $matches)) {
            return $matches[1];
        }
        // Safari/17.2.1 (version after Version/ keyword)
        if (preg_match('/Version\/(\d+\.\d+(\.\d+)?)/', $userAgent, $matches)) {
            return $matches[1];
        }
        // Edge/120.0.0.0
        if (preg_match('/Edg\/(\d+\.\d+\.\d+\.\d+)/', $userAgent, $matches)) {
            return $matches[1];
        }

        return 'Unknown';
    }

    private function detectOS(string $userAgent): string
    {
        if (preg_match('/Windows NT (\d+\.\d+)/', $userAgent, $matches)) {
            return 'Windows ' . $this->mapWindowsVersion($matches[1]);
        }
        if (str_contains($userAgent, 'Macintosh') || str_contains($userAgent, 'Mac OS X')) {
            if (preg_match('/Mac OS X (\d+[._]\d+([._]\d+)?)/', $userAgent, $matches)) {
                $version = str_replace('_', '.', $matches[1]);
                return 'macOS ' . $version;
            }
            return 'macOS';
        }
        if (str_contains($userAgent, 'Linux')) {
            return 'Linux';
        }

        return 'Unknown';
    }

    private function detectMobileOS(string $userAgent): string
    {
        if (str_contains($userAgent, 'Android')) {
            if (preg_match('/Android (\d+(\.\d+)?)/', $userAgent, $matches)) {
                return 'Android ' . $matches[1];
            }
            return 'Android';
        }
        if (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) {
            if (preg_match('/OS (\d+_\d+(_\d+)?)/', $userAgent, $matches)) {
                $version = str_replace('_', '.', $matches[1]);
                return 'iOS ' . $version;
            }
            return 'iOS';
        }

        return 'Unknown';
    }

    private function extractIOSVersion(string $userAgent): string
    {
        if (preg_match('/OS (\d+_\d+(_\d+)?)/', $userAgent, $matches)) {
            return str_replace('_', '.', $matches[1]);
        }
        return 'Unknown';
    }

    private function extractIOSDevice(string $userAgent): string
    {
        if (str_contains($userAgent, 'iPad')) {
            return 'iPad';
        }
        if (str_contains($userAgent, 'iPhone')) {
            return 'iPhone';
        }
        return 'iOS Device';
    }

    private function extractAndroidVersion(string $userAgent): string
    {
        if (preg_match('/Android (\d+(\.\d+)?)/', $userAgent, $matches)) {
            return $matches[1];
        }
        return 'Unknown';
    }

    private function extractAndroidDevice(string $userAgent): string
    {
        // Android UA содержит модель устройства, например: "SM-G991B" или "Pixel 7"
        if (preg_match('/Android.*;\s*([^)]+)\)/', $userAgent, $matches)) {
            $device = trim($matches[1]);
            // Убираем Build/ часть если есть
            $device = preg_replace('/\s+Build\/.*$/', '', $device);
            return $device ?: 'Android Device';
        }
        return 'Android Device';
    }

    private function mapWindowsVersion(string $ntVersion): string
    {
        return match ($ntVersion) {
            '10.0' => '10/11',
            '6.3' => '8.1',
            '6.2' => '8',
            '6.1' => '7',
            default => $ntVersion,
        };
    }
}
