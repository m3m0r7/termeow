<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Config;

use Termeow\Domain\Configuration\TerminalSettingsInterface;
use Termeow\Domain\Terminal\ColorSchemeInterface;

readonly class TerminalOptions implements TerminalSettingsInterface
{
    public function __construct(
        private FontSettingsInterface $font,
        private WindowSettingsInterface $window,
        private ColorSchemeInterface $colors,
    ) {
    }

    public function fontPath(): string
    {
        return $this->font->path();
    }
    public function fontIndex(): int
    {
        return $this->font->index();
    }
    public function fallbackFontPath(): ?string
    {
        return $this->font->fallbackPath();
    }
    public function fallbackFontIndex(): int
    {
        return $this->font->fallbackIndex();
    }
    public function fontSize(): int
    {
        return $this->font->size();
    }
    public function fontAntialias(): bool
    {
        return $this->font->antialias();
    }
    public function windowTitle(): string
    {
        return $this->window->title();
    }
    public function colors(): ColorSchemeInterface
    {
        return $this->colors;
    }
}
