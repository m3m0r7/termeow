<?php

declare(strict_types=1);

namespace Termeow\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Termeow\Domain\Value\TerminalDimensions;
use Termeow\Infrastructure\Cli\TermeowIdentity;
use Termeow\Infrastructure\Process\Exception\PtyStartException;
use Termeow\Infrastructure\Process\PtyInputWriter;
use Termeow\Infrastructure\Process\PtyOutputReader;
use Termeow\Infrastructure\Process\ScriptCommand;
use Termeow\Infrastructure\Process\PtyScriptSettingsInterface;
use Termeow\Infrastructure\Process\Port\PtyPipeProviderInterface;

final class PtyComponentsTest extends TestCase
{
    public function testWriterAndReaderHaveIndependentPorts(): void
    {
        $pipe = fopen('php://temp', 'w+');
        self::assertIsResource($pipe);
        $provider = new class ($pipe) implements PtyPipeProviderInterface {
            /** @param resource $pipe */
            public function __construct(private $pipe)
            {
            }
            public function inputPipe()
            {
                return $this->pipe;
            }
            public function outputPipes(): array
            {
                return [$this->pipe];
            }
        };
        $writer = new PtyInputWriter($provider);
        $reader = new PtyOutputReader($provider);
        $writer->write("日本語\x03");
        rewind($pipe);
        self::assertSame("日本語\x03", $reader->read());
        self::assertSame('', $reader->read());
        fclose($pipe);
    }

    public function testStartupScriptCanBeInjected(): void
    {
        $settings = new class implements PtyScriptSettingsInterface {
            public function scriptPath(): string
            {
                return '/bin/sh';
            }
            public function nullDevice(): string
            {
                return '/dev/null';
            }
            public function shellPath(): string
            {
                return '/bin/sh';
            }
            public function startupCommand(\Termeow\Domain\Value\TerminalDimensionsInterface $dimensions): string
            {
                return 'echo ' . $dimensions->rows() . 'x' . $dimensions->columns();
            }
        };
        $arguments = (new ScriptCommand($settings))->arguments(TerminalDimensions::of(5, 40));
        self::assertContains('echo 5x40', $arguments);
    }

    public function testMissingScriptHasSpecificException(): void
    {
        $settings = new class implements PtyScriptSettingsInterface {
            public function scriptPath(): string
            {
                return '/not/a/script';
            }
            public function nullDevice(): string
            {
                return '/dev/null';
            }
            public function shellPath(): string
            {
                return '/bin/sh';
            }
            public function startupCommand(\Termeow\Domain\Value\TerminalDimensionsInterface $dimensions): string
            {
                return '';
            }
        };
        $this->expectException(PtyStartException::class);
        (new ScriptCommand($settings))->arguments(TerminalDimensions::of(5, 40));
    }
}
