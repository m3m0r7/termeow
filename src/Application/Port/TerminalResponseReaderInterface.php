<?php

declare(strict_types=1);

namespace Termeow\Application\Port;

interface TerminalResponseReaderInterface
{
    public function readResponses(): string;
}
