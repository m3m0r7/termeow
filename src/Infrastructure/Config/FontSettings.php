<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Config;

readonly class FontSettings implements FontSettingsInterface
{
    private function __construct(
        private string $path,
        private int $index,
        private ?string $fallbackPath,
        private int $fallbackIndex,
        private int $size,
        private bool $antialias,
    ) {
    }

    public static function of(
        string $path,
        int $index,
        ?string $fallbackPath,
        int $fallbackIndex,
        int $size,
        bool $antialias,
    ): self {
        return new self($path, $index, $fallbackPath, $fallbackIndex, $size, $antialias);
    }

    public function path(): string
    {
        return $this->path;
    }
    public function index(): int
    {
        return $this->index;
    }
    public function fallbackPath(): ?string
    {
        return $this->fallbackPath;
    }
    public function fallbackIndex(): int
    {
        return $this->fallbackIndex;
    }
    public function size(): int
    {
        return $this->size;
    }
    public function antialias(): bool
    {
        return $this->antialias;
    }
}
