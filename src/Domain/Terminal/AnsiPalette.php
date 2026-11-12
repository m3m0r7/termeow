<?php

declare(strict_types=1);

namespace Termeow\Domain\Terminal;

use Termeow\Domain\Exception\InvalidTerminalValueException;

readonly class AnsiPalette implements AnsiPaletteInterface
{
    private const ANSI_COLOR_COUNT = 16;

    /** @param list<RgbColorInterface> $colors */
    private function __construct(private array $colors)
    {
        if (count($colors) !== self::ANSI_COLOR_COUNT) {
            throw new InvalidTerminalValueException('ANSI palette must contain 16 colors');
        }
    }

    /** @param list<RgbColorInterface> $colors */
    public static function fromList(array $colors): self
    {
        return new self($colors);
    }

    public function color(int $index): RgbColorInterface
    {
        return $this->colors[$index] ?? throw new InvalidTerminalValueException('Invalid ANSI color index');
    }

    public function colors(): array
    {
        return $this->colors;
    }
}
