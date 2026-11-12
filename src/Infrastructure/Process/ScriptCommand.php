<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Process;

use Termeow\Domain\Value\TerminalDimensionsInterface;
use Termeow\Infrastructure\Process\Exception\PtyStartException;
use Termeow\Infrastructure\Process\Port\PtyCommandInterface;

readonly class ScriptCommand implements PtyCommandInterface
{
    public function __construct(private PtyScriptSettingsInterface $settings)
    {
    }

    public function arguments(TerminalDimensionsInterface $dimensions): array
    {
        $script = $this->settings->scriptPath();
        if (!is_executable($script)) {
            throw new PtyStartException('The script utility is required for a controlling terminal');
        }
        $command = $this->settings->startupCommand($dimensions);
        return match (PHP_OS_FAMILY) {
            'Darwin' => [$script, '-q', $this->settings->nullDevice(), $this->settings->shellPath(), '-c', $command],
            'Linux' => [$script, '-q', '-c', $command, $this->settings->nullDevice()],
            default => throw new PtyStartException('Unsupported operating system for PTY sessions'),
        };
    }
}
