<?php

declare(strict_types=1);

namespace Termeow\Application\Port;

interface TerminalOutputWriterInterface
{
    public function write(string $data): void;
}
