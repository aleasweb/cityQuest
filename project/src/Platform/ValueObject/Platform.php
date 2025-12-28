<?php

declare(strict_types=1);

namespace App\Platform\ValueObject;

/**
 * Value Object для данных о платформе клиента.
 */
final class Platform
{
    private function __construct(
        private string $type,
        private array $metadata
    ) {
    }

    public static function unknown(): self
    {
        return new self('unknown', []);
    }

    public static function web(string $browser, string $browserVersion, string $os): self
    {
        return new self('web', [
            'browser' => $browser,
            'browser_version' => $browserVersion,
            'os' => $os,
        ]);
    }

    public static function ios(string $appVersion, string $osVersion, string $deviceModel): self
    {
        return new self('ios', [
            'app_version' => $appVersion,
            'os_version' => $osVersion,
            'device_model' => $deviceModel,
        ]);
    }

    public static function android(string $appVersion, string $osVersion, string $deviceModel): self
    {
        return new self('android', [
            'app_version' => $appVersion,
            'os_version' => $osVersion,
            'device_model' => $deviceModel,
        ]);
    }

    public static function fromArray(array $data): self
    {
        $type = $data['type'] ?? 'unknown';
        $metadata = $data;
        unset($metadata['type']);

        return new self($type, $metadata);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function toArray(): array
    {
        return array_merge(['type' => $this->type], $this->metadata);
    }

    public function isWeb(): bool
    {
        return $this->type === 'web';
    }

    public function isMobile(): bool
    {
        return in_array($this->type, ['ios', 'android'], true);
    }
}

