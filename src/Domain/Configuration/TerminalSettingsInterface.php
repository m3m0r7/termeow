<?php

declare(strict_types=1);

namespace Termeow\Domain\Configuration;

use Termeow\Domain\Terminal\ColorSchemeInterface;

interface TerminalSettingsInterface
{
    public function fontPath(): string;

    public function fontIndex(): int;

    public function fallbackFontPath(): ?string;

    public function fallbackFontIndex(): int;

    public function fontSize(): int;

    public function fontAntialias(): bool;

    public function windowTitle(): string;

    public function colors(): ColorSchemeInterface;
}
