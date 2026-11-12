<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Process;

use Termeow\Infrastructure\Process\Exception\PtyStartException;
use Termeow\Domain\Value\TerminalDimensionsInterface;
use Termeow\Domain\Value\WorkingDirectoryInterface;
use Termeow\Infrastructure\Process\Port\PtyCommandInterface;
use Termeow\Infrastructure\Process\Port\PtyEnvironmentInterface;
use Termeow\Infrastructure\Process\Port\PtyProcessInterface;

class NativePtyProcess implements PtyProcessInterface
{
    private const GRACEFUL_SIGNAL = 1;
    private const FORCE_SIGNAL = 9;
    private const GRACE_PERIOD_MICROSECONDS = 100000;
    private const INPUT_DESCRIPTOR = 0;
    private const OUTPUT_DESCRIPTOR = 1;
    private const ERROR_DESCRIPTOR = 2;

    /** @var resource|null */
    private $process = null;
    /** @var array<int, resource> */
    private array $pipes = [];

    public function __construct(
        PtyCommandInterface $command,
        PtyEnvironmentInterface $environment,
        TerminalDimensionsInterface $dimensions,
        WorkingDirectoryInterface $directory,
    ) {
        $pipes = [];
        $process = proc_open(
            $command->arguments($dimensions),
            [
                self::INPUT_DESCRIPTOR => ['pipe', 'r'],
                self::OUTPUT_DESCRIPTOR => ['pipe', 'w'],
                self::ERROR_DESCRIPTOR => ['pipe', 'w'],
            ],
            $pipes,
            $directory->path(),
            $environment->variables($dimensions),
        );
        if (!is_resource($process)) {
            throw new PtyStartException('Could not create a pseudo terminal');
        }
        $this->setProcess($process);
        $this->setPipes($pipes);
        foreach ($this->pipes as $pipe) {
            stream_set_blocking($pipe, false);
        }
    }

    /** @param resource|null $process */
    private function setProcess($process): void
    {
        $this->process = $process;
    }

    /** @param array<int, resource> $pipes */
    private function setPipes(array $pipes): void
    {
        $this->pipes = $pipes;
    }

    /** @return resource */
    public function inputPipe()
    {
        return $this->pipes[self::INPUT_DESCRIPTOR];
    }

    /** @return list<resource> */
    public function outputPipes(): array
    {
        return [$this->pipes[self::OUTPUT_DESCRIPTOR], $this->pipes[self::ERROR_DESCRIPTOR]];
    }

    /** @phpstan-impure */
    public function running(): bool
    {
        return is_resource($this->process) && proc_get_status($this->process)['running'];
    }

    public function close(): void
    {
        if (!is_resource($this->process)) {
            return;
        }
        $process = $this->process;
        if ($this->running()) {
            proc_terminate($process, self::GRACEFUL_SIGNAL);
            usleep(self::GRACE_PERIOD_MICROSECONDS);
            if ($this->running()) {
                proc_terminate($process, self::FORCE_SIGNAL);
            }
        }
        foreach ($this->pipes as $pipe) {
            fclose($pipe);
        }
        proc_close($process);
        $this->setProcess(null);
        $this->setPipes([]);
    }

    public function __destruct()
    {
        $this->close();
    }
}
