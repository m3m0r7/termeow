<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Config;

use Termeow\Application\Port\LoadedConfigurationInterface;

readonly class LoadedConfiguration implements LoadedConfigurationInterface
{
    /** @param array<string, mixed> $values
     *  @param array<string, string> $directories
     */
    private function __construct(
        private array $values,
        private array $directories,
    ) {
    }

    /** @param array<string, mixed> $values
     *  @param array<string, string> $directories
     */
    public static function of(array $values, array $directories): self
    {
        return new self($values, $directories);
    }

    public function values(): array
    {
        return $this->values;
    }

    public function sourceDirectory(string $key): string
    {
        return $this->directories[$key] ?? throw new \LogicException("Missing source for {$key}");
    }
}
