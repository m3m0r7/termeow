<?php

declare(strict_types=1);

namespace Termeow\Domain\Terminal;

readonly class ColorScheme implements ColorSchemeInterface
{
    public function __construct(
        public RgbColorInterface $foreground,
        public RgbColorInterface $background,
        public AnsiPaletteInterface $ansi,
    ) {
    }

    public function foreground(): RgbColorInterface
    {
        return $this->foreground;
    }
    public function background(): RgbColorInterface
    {
        return $this->background;
    }
    public function ansi(int $index): RgbColorInterface
    {
        return $this->ansi->color($index);
    }
    public function palette(): array
    {
        return $this->ansi->colors();
    }
}
