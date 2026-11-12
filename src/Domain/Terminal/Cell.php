<?php

declare(strict_types=1);

namespace Termeow\Domain\Terminal;

final readonly class Cell
{
    public function __construct(
        public int $row,
        public int $column,
        public int $width,
        public string $text,
        public ?RgbColor $foreground,
        public ?RgbColor $background,
        public bool $reverse,
        public bool $bold,
        public bool $italic,
    ) {
    }
}
