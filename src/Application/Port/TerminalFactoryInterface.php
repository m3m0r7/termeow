<?php

declare(strict_types=1);

namespace Termeow\Application\Port;

use Termeow\Domain\Value\WorkingDirectoryInterface;
use Termeow\Domain\Configuration\TerminalSettingsInterface;

interface TerminalFactoryInterface
{
    public function create(
        TerminalSettingsInterface $options,
        WorkingDirectoryInterface $directory,
    ): TerminalApplicationInterface;
}
