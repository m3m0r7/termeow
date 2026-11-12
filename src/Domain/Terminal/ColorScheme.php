<?php

declare(strict_types=1);

namespace Termeow\Domain\Terminal;

final readonly class ColorScheme
{
    /** @param list<RgbColor> $ansi */
    public function __construct(
        public RgbColor $foreground,
        public RgbColor $background,
        public array $ansi,
    ) {
        if (count($ansi) !== 16) {
            throw new \InvalidArgumentException('ANSI palette must contain 16 colors');
        }
    }
}
