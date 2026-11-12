<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Process;

use Termeow\Application\Port\SessionFactoryInterface;
use Termeow\Application\Port\SessionInterface;
use Termeow\Domain\Value\TerminalDimensionsInterface;
use Termeow\Domain\Value\WorkingDirectoryInterface;
use Termeow\Infrastructure\Process\Port\PtyCommandInterface;
use Termeow\Infrastructure\Process\Port\PtyEnvironmentInterface;

readonly class ScriptPtySessionFactory implements SessionFactoryInterface
{
    public function __construct(
        private PtyCommandInterface $command,
        private PtyEnvironmentInterface $environment,
    ) {
    }

    public function create(
        TerminalDimensionsInterface $dimensions,
        WorkingDirectoryInterface $directory,
    ): SessionInterface {
        $process = new NativePtyProcess($this->command, $this->environment, $dimensions, $directory);
        return new ScriptPtySession(new PtyInputWriter($process), new PtyOutputReader($process), $process);
    }
}
