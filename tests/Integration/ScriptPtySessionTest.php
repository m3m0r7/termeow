<?php

declare(strict_types=1);

namespace Termeow\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Termeow\Infrastructure\Process\ScriptPtySession;
use Termeow\Infrastructure\Process\NativePtyProcess;
use Termeow\Infrastructure\Process\PtyInputWriter;
use Termeow\Infrastructure\Process\PtyOutputReader;
use Termeow\Infrastructure\Process\ScriptCommand;
use Termeow\Infrastructure\Process\DefaultPtyScriptSettings;
use Termeow\Infrastructure\Cli\TermeowIdentity;
use Termeow\Infrastructure\Process\TerminalEnvironment;
use Termeow\Domain\Value\TerminalDimensions;
use Termeow\Domain\Value\WorkingDirectory;

final class ScriptPtySessionTest extends TestCase
{
    public function testShellCommandsVersionAndEnvironment(): void
    {
        $directory = dirname(__DIR__, 2);
        $previousShell = getenv('SHELL');
        putenv('SHELL=/bin/sh');
        $session = $this->createSession(30, 100, $directory);
        try {
            self::assertStringContainsString($directory, $this->command($session, 'pwd', $directory));
            self::assertStringContainsString('README.md', $this->command($session, 'ls', 'README.md'));
            $japanese = $this->command($session, "printf '%s\\n' 日本語入力OK", "\r\n日本語入力OK\r\n");
            self::assertStringContainsString("\r\n日本語入力OK\r\n", $japanese);
            $version = \Composer\InstalledVersions::getPrettyVersion('m3m0r7/termeow') ?? '0.1.0';
            $environmentOutput = $this->command(
                $session,
                'printf "%s\\n" "$TERM_PROGRAM:$TERM_PROGRAM_VERSION"',
                "termeow:{$version}",
            );
            self::assertStringContainsString("termeow:{$version}", $environmentOutput);
            $versionOutput = $this->command($session, 'termeow -v', 'Built with:');
            self::assertStringContainsString("Termeow {$version}", $versionOutput);
            if (trim((string) shell_exec('command -v sl')) !== '') {
                $session->write("sl\r");
                $output = $this->waitFor($session, '(@@)', 15);
                self::assertStringContainsString("\x1b[", $output);
                self::assertTrue($this->waitForPrompt($session, $output, 15));
            }
        } finally {
            $session->close();
            $previousShell === false ? putenv('SHELL') : putenv("SHELL={$previousShell}");
        }
    }

    public function testControlBytesReachTheTerminal(): void
    {
        $previousShell = getenv('SHELL');
        putenv('SHELL=/bin/sh');
        $session = $this->createSession(5, 40, dirname(__DIR__, 2));
        try {
            usleep(100000);
            $session->read();
            $session->write("sleep 3\r");
            usleep(200000);
            $session->write("\x03printf 'CTRL_C_OK\\n'\r");
            $this->waitFor($session, 'CTRL_C_OK', 2);
            $session->write("stty -icanon -echo -isig; od -An -tx1 -N1; stty sane\r");
            usleep(200000);
            $session->read();
            $session->write("\x0b");
            self::assertStringContainsString(' 0b', $this->waitFor($session, ' 0b', 2));
        } finally {
            $session->close();
            $previousShell === false ? putenv('SHELL') : putenv("SHELL={$previousShell}");
        }
    }

    private function createSession(int $rows, int $columns, string $directory): ScriptPtySession
    {
        $process = new NativePtyProcess(
            new ScriptCommand(new DefaultPtyScriptSettings()),
            new TerminalEnvironment(
                new TermeowIdentity(),
                WorkingDirectory::at(dirname(__DIR__, 2)),
                new DefaultPtyScriptSettings(),
            ),
            TerminalDimensions::of($rows, $columns),
            WorkingDirectory::at($directory),
        );
        return new ScriptPtySession(new PtyInputWriter($process), new PtyOutputReader($process), $process);
    }

    private function command(ScriptPtySession $session, string $command, string $needle): string
    {
        $session->write($command . "\r");
        return $this->waitFor($session, $needle);
    }

    private function waitFor(ScriptPtySession $session, string $needle, float $seconds = 5): string
    {
        $output = '';
        $deadline = microtime(true) + $seconds;
        do {
            $output .= $session->read();
            if (str_contains($output, $needle)) {
                return $output;
            }
            usleep(20000);
        } while (microtime(true) < $deadline);
        self::fail("Timed out waiting for {$needle}: {$output}");
    }

    private function waitForPrompt(ScriptPtySession $session, string $output, float $seconds): bool
    {
        $deadline = microtime(true) + $seconds;
        do {
            $output = substr($output . $session->read(), -2048);
            if (preg_match('/[$#] $/', $output) === 1) {
                return true;
            }
            usleep(30000);
        } while (microtime(true) < $deadline);
        return false;
    }
}
