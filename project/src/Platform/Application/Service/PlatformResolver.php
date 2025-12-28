<?php

namespace App\Platform\Application\Service;

use App\Platform\ValueObject\Platform;
use Symfony\Component\HttpFoundation\RequestStack;

class PlatformResolver
{
    private ?RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function resolve(): Platform
    {
        // Получаем текущий активный запрос
        $currentRequest = $this->requestStack->getCurrentRequest();

        if ($currentRequest === null) {
            // Обработка ситуации, когда запроса нет
            // (например, сервис вызывается из командной строки)
            return Platform::unknown();
        }

        $userAgent = $currentRequest->headers->get('User-Agent', 'Unknown');

        // @todo добавить определение мобильных приложений
        return Platform::web(
            browser: $this->detectBrowser($userAgent),
            browserVersion: 'Unknown',
            os: $this->detectOS($userAgent)
        );
    }

    private function detectBrowser(string $userAgent): string
    {
        if (str_contains($userAgent, 'Chrome')) return 'Chrome';
        if (str_contains($userAgent, 'Firefox')) return 'Firefox';
        if (str_contains($userAgent, 'Safari')) return 'Safari';
        if (str_contains($userAgent, 'Edge')) return 'Edge';
        return 'Unknown';
    }

    private function detectOS(string $userAgent): string
    {
        if (str_contains($userAgent, 'Windows')) return 'Windows';
        if (str_contains($userAgent, 'Mac')) return 'macOS';
        if (str_contains($userAgent, 'Linux')) return 'Linux';
        if (str_contains($userAgent, 'Android')) return 'Android';
        if (str_contains($userAgent, 'iOS')) return 'iOS';
        return 'Unknown';
    }
}
