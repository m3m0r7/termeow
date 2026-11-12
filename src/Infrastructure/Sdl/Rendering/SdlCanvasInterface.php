<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Rendering;

use Termeow\Domain\Terminal\RgbColorInterface;

interface SdlCanvasInterface
{
    public function fill(int $x, int $y, int $width, int $height, RgbColorInterface $color): void;
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
