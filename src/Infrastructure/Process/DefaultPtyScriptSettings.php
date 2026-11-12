<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Process;

use Termeow\Domain\Value\TerminalDimensionsInterface;

readonly class DefaultPtyScriptSettings implements PtyScriptSettingsInterface
{
    private const SCRIPT_PATH = '/usr/bin/script';
    private const NULL_DEVICE = '/dev/null';
    private const SHELL_PATH = '/bin/sh';
    private const STARTUP_TEMPLATE = 'stty rows %d cols %d; exec "$SHELL" -i';

    public function scriptPath(): string
    {
        return self::SCRIPT_PATH;
    }
    public function nullDevice(): string
    {
        return self::NULL_DEVICE;
    }
    public function shellPath(): string
    {
        return self::SHELL_PATH;
    }

    public function startupCommand(TerminalDimensionsInterface $dimensions): string
    {
        return sprintf(self::STARTUP_TEMPLATE, $dimensions->rows(), $dimensions->columns());
    }
}
