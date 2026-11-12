<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Rendering;

use Pnlx\LibsdlTtf\Types\TTF_Font;
use Pnlx\Libsdl\Types\SDL_Renderer;

interface SdlGlyphContextInterface
{
    public function nativeRenderer(): SDL_Renderer;
    public function primaryFont(): TTF_Font;
    public function fallbackFont(): ?TTF_Font;
    public function lineHeight(): int;
    public function antialias(): bool;
}
