<?php

declare(strict_types=1);

namespace Termeow\Application\Port;

interface TerminalIdentityInterface
{
    public function name(): string;
    public function packageName(): string;
    public function version(): string;
    public function terminalType(): string;
}
