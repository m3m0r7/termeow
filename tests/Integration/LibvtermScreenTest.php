<?php

declare(strict_types=1);

namespace Termeow\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Termeow\Domain\Terminal\RgbColor;
use Termeow\Infrastructure\Config\TerminalOptions;
use Termeow\Infrastructure\Emulation\LibvtermScreen;

final class LibvtermScreenTest extends TestCase
{
    public function testAnsiColorsStylesAndWideGlyphs(): void
    {
        $colors = TerminalOptions::fromFile(dirname(__DIR__, 2) . '/termeow.toml')->colors;
        $screen = new LibvtermScreen(2, 20, $colors);
        $screen->write(
            "\x1b[31mR\x1b[91mB\x1b[0m\x1b[48;2;12;34;56m \x1b[0m\x1b[7mV\x1b[0m"
            . "\x1b[38;5;196mI\x1b[0m\x1b[38;2;10;20;30mT\r\n漢A \u{E0B0}",
        );
        $cells = iterator_to_array($screen->cells());
        self::assertEquals($colors->ansi[1], $cells[0]->foreground);
        self::assertEquals($colors->ansi[9], $cells[1]->foreground);
        self::assertEquals(new RgbColor(12, 34, 56), $cells[2]->background);
        self::assertTrue($cells[3]->reverse);
        self::assertEquals(new RgbColor(255, 0, 0), $cells[4]->foreground);
        self::assertEquals(new RgbColor(10, 20, 30), $cells[5]->foreground);
        self::assertSame(2, $cells[6]->width);
        self::assertStringContainsString("\u{E0B0}", $screen->rowText(1));
    }

    public function testBoldAndItalicAttributes(): void
    {
        $colors = TerminalOptions::fromFile(dirname(__DIR__, 2) . '/termeow.toml')->colors;
        $screen = new LibvtermScreen(1, 3, $colors);
        $screen->write("\x1b[1mB\x1b[0m\x1b[3mI\x1b[0m");
        $cells = iterator_to_array($screen->cells());
        self::assertTrue($cells[0]->bold);
        self::assertFalse($cells[0]->italic);
        self::assertFalse($cells[1]->bold);
        self::assertTrue($cells[1]->italic);
    }
}
