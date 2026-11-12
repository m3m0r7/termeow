<?php

declare(strict_types=1);

namespace Termeow\Application\Port;

interface TerminalRunnerInterface
{
    public function runTerminal(): void;
}
