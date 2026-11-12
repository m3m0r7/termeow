<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Process\Port;

use Termeow\Domain\Value\TerminalDimensionsInterface;

interface PtyCommandInterface
{
    /** @return list<string> */
    public function arguments(TerminalDimensionsInterface $dimensions): array;
}
