<?php

declare(strict_types=1);

namespace Termeow\Domain\Terminal;

interface AnsiPaletteInterface
{
    public function color(int $index): RgbColorInterface;

    /** @return list<RgbColorInterface> */
    public function colors(): array;
}
