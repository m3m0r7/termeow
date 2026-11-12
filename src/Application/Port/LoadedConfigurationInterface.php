<?php

declare(strict_types=1);

namespace Termeow\Application\Port;

interface LoadedConfigurationInterface
{
    /** @return array<string, mixed> */
    public function values(): array;

    public function sourceDirectory(string $key): string;
}
