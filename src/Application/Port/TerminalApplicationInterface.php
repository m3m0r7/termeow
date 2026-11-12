<?php

declare(strict_types=1);

namespace Termeow\Application\Port;

interface TerminalApplicationInterface
{
    public function run(): void;
}
