<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Rendering;

use Pnlx\Libsdl\Types\SDL_Renderer;
use Termeow\Domain\Terminal\RgbColorInterface;

interface SdlWindowInterface
{
    public function nativeRenderer(): SDL_Renderer;
    public function padding(): int;
    public function clear(RgbColorInterface $color): void;
    public function fill(int $x, int $y, int $width, int $height, RgbColorInterface $color): void;
    public function positionTextInput(int $row, int $column): void;
    public function present(): void;
}
