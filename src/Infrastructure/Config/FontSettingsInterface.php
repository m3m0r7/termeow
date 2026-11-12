<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Config;

interface FontSettingsInterface
{
    public function path(): string;
    public function index(): int;
    public function fallbackPath(): ?string;
    public function fallbackIndex(): int;
    public function size(): int;
    public function antialias(): bool;
}
