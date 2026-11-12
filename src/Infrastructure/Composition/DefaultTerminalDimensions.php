<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Composition;

use Termeow\Domain\Value\TerminalDimensionsInterface;

readonly class DefaultTerminalDimensions implements TerminalDimensionsInterface
{
    private const ROWS = 30;
    private const COLUMNS = 100;

    public function rows(): int
    {
        return self::ROWS;
    }
    public function columns(): int
    {
        return self::COLUMNS;
    }
}
