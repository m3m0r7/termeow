<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Process\Port;

use Termeow\Domain\Value\TerminalDimensionsInterface;

interface PtyEnvironmentInterface
{
    /** @return array<string, string> */
    public function variables(TerminalDimensionsInterface $dimensions): array;
}
