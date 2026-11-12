<?php

declare(strict_types=1);

namespace Termeow\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Termeow\Application\Port\TerminalViewInterface;
use Termeow\Application\TextComposition;
use Termeow\Domain\Terminal\Cursor;
use Termeow\Infrastructure\Sdl\Rendering\SdlCanvasInterface;
use Termeow\Infrastructure\Sdl\Rendering\SdlCompositionPainter;
use Termeow\Infrastructure\Sdl\Rendering\TerminalMetricsInterface;
use Termeow\Tests\Support\TestOptions;

final class SdlCompositionPainterTest extends TestCase
{
    public function testWideCompositionAdvancesTheCandidateCursorWithoutNativeSdl(): void
    {
        $screen = $this->createStub(TerminalViewInterface::class);
        $screen->method('rows')->willReturn(2);
        $screen->method('columns')->willReturn(10);
        $screen->method('cursor')->willReturn(Cursor::at(0, 4));
        $metrics = $this->createStub(TerminalMetricsInterface::class);
        $metrics->method('padding')->willReturn(8);
        $metrics->method('cellWidth')->willReturn(10);
        $metrics->method('lineHeight')->willReturn(20);
        $metrics->method('characterCells')->willReturnCallback(
            static fn (string $character): int => min(2, mb_strwidth($character, 'UTF-8')),
        );
        $canvas = $this->createMock(SdlCanvasInterface::class);
        $canvas->expects(self::atLeastOnce())->method('fill');
        $canvas->expects(self::once())->method('drawText')->with('に');
        $painter = new SdlCompositionPainter($screen, $canvas, TestOptions::fromDefault()->colors(), $metrics);
        $composition = TextComposition::of('に', 1, 0);
        self::assertSame([0, 6], $painter->cursor($composition));
        $painter->draw($composition);
    }
}
