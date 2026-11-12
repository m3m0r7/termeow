<?php

declare(strict_types=1);

namespace Termeow\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Termeow\Application\Port\InputSourceFactoryInterface;
use Termeow\Application\Port\InputSourceInterface;
use Termeow\Application\Port\RendererFactoryInterface;
use Termeow\Application\Port\RendererInterface;
use Termeow\Application\Port\ScreenFactoryInterface;
use Termeow\Application\Port\ScreenInterface;
use Termeow\Application\Port\SessionFactoryInterface;
use Termeow\Application\Port\SessionInterface;
use Termeow\Application\Port\TerminalRuntimeInitializerInterface;
use Termeow\Domain\Configuration\TerminalSettingsInterface;
use Termeow\Domain\Terminal\ColorSchemeInterface;
use Termeow\Domain\Value\TerminalDimensions;
use Termeow\Domain\Value\WorkingDirectory;
use Termeow\Infrastructure\Composition\TermeowTerminalFactory;

final class TermeowTerminalFactoryTest extends TestCase
{
    public function testAssemblyUsesOnlyPortsAndInitializesBindingsFirst(): void
    {
        $calls = [];
        $runtime = $this->createMock(TerminalRuntimeInitializerInterface::class);
        $runtime->expects(self::once())->method('initialize')->willReturnCallback(
            static function () use (&$calls): void {
                $calls[] = 'runtime';
            },
        );
        $dimensions = TerminalDimensions::of(2, 3);
        $colors = $this->createStub(ColorSchemeInterface::class);
        $settings = $this->createStub(TerminalSettingsInterface::class);
        $settings->method('colors')->willReturn($colors);
        $screen = $this->createStub(ScreenInterface::class);
        $screens = $this->createMock(ScreenFactoryInterface::class);
        $screens->expects(self::once())->method('create')->with($dimensions, $colors)
            ->willReturnCallback(static function () use (&$calls, $screen): ScreenInterface {
                $calls[] = 'screen';
                return $screen;
            });
        $renderer = $this->createStub(RendererInterface::class);
        $renderers = $this->createMock(RendererFactoryInterface::class);
        $renderers->expects(self::once())->method('create')->with($screen, $settings)
            ->willReturnCallback(static function () use (&$calls, $renderer): RendererInterface {
                $calls[] = 'renderer';
                return $renderer;
            });
        $session = $this->createStub(SessionInterface::class);
        $sessions = $this->createMock(SessionFactoryInterface::class);
        $sessions->expects(self::once())->method('create')->with($dimensions, self::anything())
            ->willReturnCallback(static function () use (&$calls, $session): SessionInterface {
                $calls[] = 'session';
                return $session;
            });
        $input = $this->createStub(InputSourceInterface::class);
        $inputs = $this->createMock(InputSourceFactoryInterface::class);
        $inputs->expects(self::once())->method('create')->willReturnCallback(
            static function () use (&$calls, $input): InputSourceInterface {
                $calls[] = 'input';
                return $input;
            },
        );
        $factory = new TermeowTerminalFactory($runtime, $dimensions, $screens, $renderers, $sessions, $inputs);
        $factory->create($settings, WorkingDirectory::at(dirname(__DIR__, 2)));
        self::assertSame(['runtime', 'screen', 'renderer', 'session', 'input'], $calls);
    }
}
