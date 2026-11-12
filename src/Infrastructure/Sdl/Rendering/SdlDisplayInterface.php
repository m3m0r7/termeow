<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Rendering;

use Termeow\Domain\Terminal\RgbColorInterface;

interface SdlDisplayInterface extends SdlGlyphContextInterface, TerminalMetricsInterface
{
    public function clear(RgbColorInterface $color): void;
    public function fill(int $x, int $y, int $width, int $height, RgbColorInterface $color): void;
    public function positionTextInput(int $row, int $column): void;
    public function present(): void;
}
