<?php

declare(strict_types=1);

namespace Termeow\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

final class CliTest extends TestCase
{
    public function testVersionAliasesShowCreditsWithoutLaunchingWindow(): void
    {
        foreach (['-v', '--version'] as $option) {
            $process = new Process([PHP_BINARY, dirname(__DIR__, 2) . '/bin/termeow', $option]);
            $process->mustRun();
            self::assertStringContainsString('Termeow ', $process->getOutput());
            self::assertStringContainsString('PHP ' . PHP_VERSION, $process->getOutput());
            self::assertStringContainsString('Built with: pnl', $process->getOutput());
        }
    }

    public function testHelpAndUnknownOptionUseSymfonyConsole(): void
    {
        $help = new Process([PHP_BINARY, dirname(__DIR__, 2) . '/bin/termeow', '--help']);
        $help->mustRun();
        self::assertStringContainsString('Launch the Termeow terminal', $help->getOutput());
        $invalid = new Process([PHP_BINARY, dirname(__DIR__, 2) . '/bin/termeow', '--unknown']);
        $invalid->run();
        self::assertNotSame(0, $invalid->getExitCode());
        self::assertStringContainsString('The "--unknown" option does not exist', $invalid->getErrorOutput());
    }
}
