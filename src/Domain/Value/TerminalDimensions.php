<?php

declare(strict_types=1);

namespace Termeow\Domain\Value;

use Termeow\Domain\Exception\InvalidTerminalValueException;

readonly class TerminalDimensions implements TerminalDimensionsInterface
{
    private const MIN_DIMENSION = 1;

    private function __construct(public int $rows, public int $columns)
    {
        if ($rows < self::MIN_DIMENSION || $columns < self::MIN_DIMENSION) {
            throw new InvalidTerminalValueException('Terminal dimensions must be positive');
        }
    }

    public static function of(int $rows, int $columns): self
    {
        return new self($rows, $columns);
    }

    public function rows(): int
    {
        return $this->rows;
    }

    public function columns(): int
    {
        return $this->columns;
    }
}
