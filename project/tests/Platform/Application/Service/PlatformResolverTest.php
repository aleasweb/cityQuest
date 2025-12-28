<?php

declare(strict_types=1);

namespace App\Tests\Platform\Application\Service;

use App\Platform\Application\Service\PlatformResolver;
use App\Platform\ValueObject\Platform;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class PlatformResolverTest extends TestCase
{
    private RequestStack $requestStack;
    private PlatformResolver $platformResolver;

    protected function setUp(): void
    {
        $this->requestStack = new RequestStack();
        $this->platformResolver = new PlatformResolver($this->requestStack);
    }

    public function testResolveReturnsUnknownWhenNoRequest(): void
    {
        // Act
        $platform = $this->platformResolver->resolve();

        // Assert
        $this->assertEquals('unknown', $platform->getType());
        $this->assertEmpty($platform->getMetadata());
    }

    /**
     * @dataProvider desktopBrowserProvider
     */
    public function testResolveDesktopBrowser(string $userAgent, string $expectedBrowser, string $expectedVersion, string $expectedOS): void
    {
        // Arrange
        $request = $this->createRequestWithUserAgent($userAgent);
        $this->requestStack->push($request);

        // Act
        $platform = $this->platformResolver->resolve();

        // Assert
        $this->assertTrue($platform->isWeb());
        $this->assertFalse($platform->isMobile());
        $metadata = $platform->getMetadata();
        $this->assertEquals($expectedBrowser, $metadata['browser']);
        $this->assertEquals($expectedVersion, $metadata['browser_version']);
        $this->assertEquals($expectedOS, $metadata['os']);
    }

    public static function desktopBrowserProvider(): array
    {
        return [
            'Chrome on Windows 10' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.6099.129 Safari/537.36',
                'Chrome',
                '120.0.6099.129',
                'Windows 10/11',
            ],
            'Firefox on macOS' => [
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Firefox/120.0',
                'Firefox',
                '120.0',
                'macOS 10.15.7',
            ],
            'Safari on macOS' => [
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2.1 Safari/605.1.15',
                'Safari',
                '17.2.1',
                'macOS 10.15.7',
            ],
            'Edge on Windows' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0',
                'Edge',
                '120.0.0.0',
                'Windows 10/11',
            ],
            'Opera on Linux' => [
                'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36 OPR/105.0.0.0',
                'Opera',
                '119.0.0.0', // Chrome version extracted (Opera UA contains Chrome version)
                'Linux',
            ],
            'Chrome on Windows 7' => [
                'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36',
                'Chrome',
                '109.0.0.0',
                'Windows 7',
            ],
        ];
    }

    /**
     * @dataProvider mobileBrowserProvider
     */
    public function testResolveMobileBrowser(string $userAgent, string $expectedBrowser, string $expectedOS): void
    {
        // Arrange
        $request = $this->createRequestWithUserAgent($userAgent);
        $this->requestStack->push($request);

        // Act
        $platform = $this->platformResolver->resolve();

        // Assert
        $this->assertTrue($platform->isWeb());
        $metadata = $platform->getMetadata();
        $this->assertStringContainsString('Mobile', $metadata['browser']);
        $this->assertEquals($expectedBrowser, $metadata['browser']);
        $this->assertEquals($expectedOS, $metadata['os']);
    }

    public static function mobileBrowserProvider(): array
    {
        return [
            'Chrome on Android' => [
                'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.6099.144 Mobile Safari/537.36',
                'Chrome Mobile',
                'Android 13',
            ],
            'Safari on iPhone' => [
                'Mozilla/5.0 (iPhone; CPU iPhone OS 17_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Mobile/15E148 Safari/604.1',
                'Safari Mobile',
                'iOS 17.2',
            ],
            'Safari on iPad' => [
                'Mozilla/5.0 (iPad; CPU OS 16_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.4 Mobile/15E148 Safari/604.1',
                'Safari Mobile',
                'iOS 16.4',
            ],
            'Firefox on Android' => [
                'Mozilla/5.0 (Android 12; Mobile; rv:109.0) Gecko/109.0 Firefox/109.0',
                'Firefox Mobile',
                'Android 12',
            ],
        ];
    }

    /**
     * @dataProvider nativeAppProvider
     */
    public function testResolveNativeApp(string $appPlatformHeader, string $userAgent, string $expectedType, string $expectedAppVersion): void
    {
        // Arrange
        $request = $this->createRequestWithHeaders([
            'User-Agent' => $userAgent,
            'X-App-Platform' => $appPlatformHeader,
        ]);
        $this->requestStack->push($request);

        // Act
        $platform = $this->platformResolver->resolve();

        // Assert
        $this->assertEquals($expectedType, $platform->getType());
        $this->assertTrue($platform->isMobile());
        $metadata = $platform->getMetadata();
        $this->assertEquals($expectedAppVersion, $metadata['app_version']);
        $this->assertArrayHasKey('os_version', $metadata);
        $this->assertArrayHasKey('device_model', $metadata);
    }

    public static function nativeAppProvider(): array
    {
        return [
            'iOS App' => [
                'ios/1.2.3',
                'CityQuest/1.2.3 (iPhone; iOS 17.2; Scale/3.00)',
                'ios',
                '1.2.3',
            ],
            'Android App' => [
                'android/2.0.5',
                'CityQuest/2.0.5 (Linux; Android 13; Pixel 7 Build/TQ3A.230805.001)',
                'android',
                '2.0.5',
            ],
        ];
    }

    public function testResolveIOSAppExtractsDeviceModel(): void
    {
        // Arrange
        $request = $this->createRequestWithHeaders([
            'User-Agent' => 'CityQuest/1.0.0 (iPhone; CPU iPhone OS 17_2 like Mac OS X)',
            'X-App-Platform' => 'ios/1.0.0',
        ]);
        $this->requestStack->push($request);

        // Act
        $platform = $this->platformResolver->resolve();

        // Assert
        $this->assertEquals('ios', $platform->getType());
        $metadata = $platform->getMetadata();
        $this->assertEquals('1.0.0', $metadata['app_version']);
        $this->assertEquals('17.2', $metadata['os_version']);
        $this->assertEquals('iPhone', $metadata['device_model']);
    }

    public function testResolveIOSAppDetectsIPad(): void
    {
        // Arrange
        $request = $this->createRequestWithHeaders([
            'User-Agent' => 'CityQuest/1.0.0 (iPad; CPU OS 16_4 like Mac OS X)',
            'X-App-Platform' => 'ios/1.0.0',
        ]);
        $this->requestStack->push($request);

        // Act
        $platform = $this->platformResolver->resolve();

        // Assert
        $metadata = $platform->getMetadata();
        $this->assertEquals('iPad', $metadata['device_model']);
        $this->assertEquals('16.4', $metadata['os_version']);
    }

    public function testResolveAndroidAppExtractsDeviceModel(): void
    {
        // Arrange
        $request = $this->createRequestWithHeaders([
            'User-Agent' => 'CityQuest/2.0.0 (Linux; Android 13; SM-G991B Build/TP1A.220624.014)',
            'X-App-Platform' => 'android/2.0.0',
        ]);
        $this->requestStack->push($request);

        // Act
        $platform = $this->platformResolver->resolve();

        // Assert
        $this->assertEquals('android', $platform->getType());
        $metadata = $platform->getMetadata();
        $this->assertEquals('2.0.0', $metadata['app_version']);
        $this->assertEquals('13', $metadata['os_version']);
        $this->assertEquals('SM-G991B', $metadata['device_model']);
    }

    public function testResolveAndroidAppWithPixelDevice(): void
    {
        // Arrange
        $request = $this->createRequestWithHeaders([
            'User-Agent' => 'CityQuest/2.0.0 (Linux; Android 14; Pixel 7 Pro)',
            'X-App-Platform' => 'android/2.0.0',
        ]);
        $this->requestStack->push($request);

        // Act
        $platform = $this->platformResolver->resolve();

        // Assert
        $metadata = $platform->getMetadata();
        $this->assertEquals('Pixel 7 Pro', $metadata['device_model']);
        $this->assertEquals('14', $metadata['os_version']);
    }

    public function testResolveWithInvalidAppPlatformHeaderReturnsUnknown(): void
    {
        // Arrange
        $request = $this->createRequestWithHeaders([
            'User-Agent' => 'Some Random User Agent',
            'X-App-Platform' => 'invalid/format/here',
        ]);
        $this->requestStack->push($request);

        // Act
        $platform = $this->platformResolver->resolve();

        // Assert
        $this->assertEquals('unknown', $platform->getType());
    }

    public function testResolveWithUnknownBrowser(): void
    {
        // Arrange
        $request = $this->createRequestWithUserAgent('Some Unknown Browser/1.0');
        $this->requestStack->push($request);

        // Act
        $platform = $this->platformResolver->resolve();

        // Assert
        $this->assertTrue($platform->isWeb());
        $metadata = $platform->getMetadata();
        $this->assertEquals('Unknown', $metadata['browser']);
        $this->assertEquals('Unknown', $metadata['browser_version']);
    }

    public function testResolveEdgeBrowserBeforeChrome(): void
    {
        // Arrange - Edge User-Agent содержит Chrome, но должен определяться как Edge
        $request = $this->createRequestWithUserAgent(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0'
        );
        $this->requestStack->push($request);

        // Act
        $platform = $this->platformResolver->resolve();

        // Assert
        $metadata = $platform->getMetadata();
        $this->assertEquals('Edge', $metadata['browser']);
        $this->assertNotEquals('Chrome', $metadata['browser']);
    }

    public function testResolveWindowsVersionMapping(): void
    {
        // Arrange
        $testCases = [
            'Windows NT 10.0' => 'Windows 10/11',
            'Windows NT 6.3' => 'Windows 8.1',
            'Windows NT 6.2' => 'Windows 8',
            'Windows NT 6.1' => 'Windows 7',
        ];

        foreach ($testCases as $ntVersion => $expectedVersion) {
            $request = $this->createRequestWithUserAgent(
                "Mozilla/5.0 ($ntVersion; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0"
            );
            $this->requestStack->push($request);

            // Act
            $platform = $this->platformResolver->resolve();

            // Assert
            $metadata = $platform->getMetadata();
            $this->assertEquals($expectedVersion, $metadata['os'], "Failed for $ntVersion");

            // Cleanup
            $this->requestStack->pop();
        }
    }

    public function testResolveWithEmptyUserAgent(): void
    {
        // Arrange
        $request = $this->createRequestWithUserAgent('');
        $this->requestStack->push($request);

        // Act
        $platform = $this->platformResolver->resolve();

        // Assert
        $this->assertTrue($platform->isWeb());
        $metadata = $platform->getMetadata();
        $this->assertEquals('Unknown', $metadata['browser']);
    }

    private function createRequestWithUserAgent(string $userAgent): Request
    {
        return $this->createRequestWithHeaders(['User-Agent' => $userAgent]);
    }

    private function createRequestWithHeaders(array $headers): Request
    {
        $request = new Request();
        $headerBag = new HeaderBag($headers);
        
        // Используем рефлексию для установки headers, так как HeaderBag immutable
        $reflection = new \ReflectionClass($request);
        $headersProperty = $reflection->getProperty('headers');
        $headersProperty->setAccessible(true);
        $headersProperty->setValue($request, $headerBag);

        return $request;
    }
}

