<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Rendering;

use Pnlx\Libsdl\Types\SDL_Renderer;
use Pnlx\LibsdlTtf\Types\TTF_Font;
use Termeow\Domain\Terminal\RgbColorInterface;

readonly class SdlDisplay implements SdlDisplayInterface
{
    public function __construct(
        private SdlFontSetInterface $fonts,
        private SdlWindowInterface $window,
    ) {
    }

    public function nativeRenderer(): SDL_Renderer
    {
        return $this->window->nativeRenderer();
    }
    public function primaryFont(): TTF_Font
    {
        return $this->fonts->primaryFont();
    }
    public function fallbackFont(): ?TTF_Font
    {
        return $this->fonts->fallbackFont();
    }
    public function padding(): int
    {
        return $this->window->padding();
    }
    public function cellWidth(): int
    {
        return $this->fonts->cellWidth();
    }
    public function lineHeight(): int
    {
        return $this->fonts->lineHeight();
    }
    public function antialias(): bool
    {
        return $this->fonts->antialias();
    }
    public function characterCells(string $character): int
    {
        return $this->fonts->characterCells($character);
    }
    public function clear(RgbColorInterface $color): void
    {
        $this->window->clear($color);
    }
    public function fill(int $x, int $y, int $width, int $height, RgbColorInterface $color): void
    {
        $this->window->fill($x, $y, $width, $height, $color);
    }
    public function positionTextInput(int $row, int $column): void
    {
        $this->window->positionTextInput($row, $column);
    }
    public function present(): void
    {
        $this->window->present();
    }
}
