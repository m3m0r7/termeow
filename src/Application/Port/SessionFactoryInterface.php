<?php

declare(strict_types=1);

namespace Termeow\Application\Port;

use Termeow\Domain\Value\TerminalDimensionsInterface;
use Termeow\Domain\Value\WorkingDirectoryInterface;

interface SessionFactoryInterface
{
    public function create(
        TerminalDimensionsInterface $dimensions,
        WorkingDirectoryInterface $directory,
    ): SessionInterface;
}
