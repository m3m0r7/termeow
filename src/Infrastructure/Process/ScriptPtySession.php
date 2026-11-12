<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Process;

use Composer\InstalledVersions;
use Termeow\Application\Port\Session;

final class ScriptPtySession implements Session
{
    /** @var resource|null */
    private $process;
    /** @var array<int, resource> */
    private array $pipes = [];

    public function __construct(int $rows, int $columns, string $directory)
    {
        $shell = getenv('SHELL') ?: '/bin/sh';
        if (!is_executable($shell)) {
            throw new \RuntimeException("Shell is not executable: {$shell}");
        }
        if (!is_dir($directory)) {
            throw new \RuntimeException("Working directory does not exist: {$directory}");
        }
        $environment = getenv();
        $environment['SHELL'] = $shell;
        $environment['TERM'] = 'xterm-256color';
        $environment['TERM_PROGRAM'] = 'termeow';
        $environment['TERM_PROGRAM_VERSION'] = InstalledVersions::getPrettyVersion('m3m0r7/termeow') ?? '0.1.0-dev';
        $path = $environment['PATH'] ?? '';
        $bin = dirname(__DIR__, 3) . '/bin';
        $environment['PATH'] = in_array($bin, explode(PATH_SEPARATOR, $path), true)
            ? $path
            : $bin . ($path === '' ? '' : PATH_SEPARATOR . $path);
        $environment['COLUMNS'] = (string) $columns;
        $environment['LINES'] = (string) $rows;
        $command = sprintf('stty rows %d cols %d; exec "$SHELL" -i', $rows, $columns);
        $script = '/usr/bin/script';
        if (!is_executable($script)) {
            throw new \RuntimeException('The script utility is required for a controlling terminal');
        }
        $arguments = match (PHP_OS_FAMILY) {
            'Darwin' => [$script, '-q', '/dev/null', '/bin/sh', '-c', $command],
            'Linux' => [$script, '-q', '-c', $command, '/dev/null'],
            default => throw new \RuntimeException('Unsupported operating system for PTY sessions'),
        };
        $process = proc_open(
            $arguments,
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $this->pipes,
            $directory,
            $environment,
        );
        if (!is_resource($process)) {
            throw new \RuntimeException('Could not create a pseudo terminal');
        }
        $this->process = $process;
        foreach ($this->pipes as $pipe) {
            stream_set_blocking($pipe, false);
        }
    }

    public function write(string $input): void
    {
        $remaining = $input;
        while ($remaining !== '') {
            $written = fwrite($this->pipes[0], $remaining);
            if ($written === false || $written === 0) {
                throw new \RuntimeException('Could not write to the pseudo terminal');
            }
            $remaining = substr($remaining, $written);
        }
    }

    public function read(): string
    {
        $output = '';
        foreach ([1, 2] as $descriptor) {
            $pipe = $this->pipes[$descriptor];
            while (($chunk = fread($pipe, 65536)) !== false && $chunk !== '') {
                $output .= $chunk;
            }
        }
        return $output;
    }

    public function running(): bool
    {
        if (!is_resource($this->process)) {
            return false;
        }
        return $this->processRunning($this->process);
    }

    public function close(): void
    {
        if (!is_resource($this->process)) {
            return;
        }
        $process = $this->process;
        if ($this->processRunning($process)) {
            proc_terminate($process, 1);
            usleep(100000);
            if ($this->processRunning($process)) {
                proc_terminate($process, 9);
            }
        }
        foreach ($this->pipes as $pipe) {
            fclose($pipe);
        }
        proc_close($process);
        $this->process = null;
    }

    /**
     * @param resource $process
     * @phpstan-impure
     */
    private function processRunning($process): bool
    {
        return proc_get_status($process)['running'];
    }

    public function __destruct()
    {
        $this->close();
    }
}
