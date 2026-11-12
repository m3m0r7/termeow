<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Rendering;

interface TerminalMetricsInterface
{
    public function padding(): int;
    public function cellWidth(): int;
    public function lineHeight(): int;
    public function characterCells(string $character): int;
}
