<?php

declare(strict_types=1);

namespace Termeow\Application\Port;

interface TerminalRuntimeInitializerInterface
{
    public function initialize(): void;
}
