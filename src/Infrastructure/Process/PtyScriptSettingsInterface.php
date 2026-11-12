<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Process;

use Termeow\Domain\Value\TerminalDimensionsInterface;

interface PtyScriptSettingsInterface
{
    public function scriptPath(): string;
    public function nullDevice(): string;
    public function shellPath(): string;
    public function startupCommand(TerminalDimensionsInterface $dimensions): string;
}
