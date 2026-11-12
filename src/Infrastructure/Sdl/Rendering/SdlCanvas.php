<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Rendering;

use Termeow\Domain\Terminal\RgbColorInterface;

readonly class SdlCanvas implements SdlCanvasInterface
{
    public function __construct(
        private SdlDisplayInterface $display,
        private GlyphPainterInterface $glyphs,
    ) {
    }

    public function fill(int $x, int $y, int $width, int $height, RgbColorInterface $color): void
    {
        $this->display->fill($x, $y, $width, $height, $color);
    }

    public function drawText(
        string $text,
        int $x,
        int $y,
        int $width,
        RgbColorInterface $color,
        bool $bold,
        bool $italic,
    ): void {
        $this->glyphs->drawText($text, $x, $y, $width, $color, $bold, $italic);
    }
}
