<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Config;

use Termeow\Domain\Value\WorkingDirectoryInterface;

readonly class ConfigurationPaths implements ConfigurationPathsInterface
{
    private const CONFIG_NAME = 'termeow.toml';

    public function __construct(
        private WorkingDirectoryInterface $package,
        private WorkingDirectoryInterface $home,
        private WorkingDirectoryInterface $current,
    ) {
    }

    public function paths(): array
    {
        return array_values(array_unique([
            $this->package->path() . '/' . self::CONFIG_NAME,
            $this->home->path() . '/' . self::CONFIG_NAME,
            $this->current->path() . '/' . self::CONFIG_NAME,
        ]));
    }
}
