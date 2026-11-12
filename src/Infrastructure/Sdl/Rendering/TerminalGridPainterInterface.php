<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Rendering;

interface TerminalGridPainterInterface
{
    public function draw(bool $showCursor): void;
}
