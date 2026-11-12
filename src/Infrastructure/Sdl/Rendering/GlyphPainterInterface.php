<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Rendering;

use Termeow\Domain\Terminal\RgbColorInterface;

interface GlyphPainterInterface
{
    public function drawText(
        string $text,
        int $x,
        int $y,
        int $width,
        RgbColorInterface $color,
        bool $bold,
        bool $italic,
    ): void;
}
