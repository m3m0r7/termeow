<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Rendering;

use Pnlx\LibsdlTtf\Types\TTF_Font;

interface SdlFontSetInterface
{
    public function runtime(): SdlRuntimeInterface;

    public function primaryFont(): TTF_Font;
    public function fallbackFont(): ?TTF_Font;
    public function cellWidth(): int;
    public function lineHeight(): int;
    public function antialias(): bool;
    public function characterCells(string $character): int;
}
