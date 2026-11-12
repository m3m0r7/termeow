<?php

declare(strict_types=1);

namespace Termeow\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Termeow\Domain\Value\TerminalDimensions;
use Termeow\Infrastructure\Config\FontSettings;
use Termeow\Infrastructure\Config\TerminalOptions;
use Termeow\Infrastructure\Config\WindowSettings;
use Termeow\Infrastructure\Emulation\LibvtermScreen;
use Termeow\Infrastructure\Emulation\LibvtermPaletteInstaller;
use Termeow\Infrastructure\Emulation\LibvtermCellReaderFactory;
use Termeow\Infrastructure\Sdl\Exception\SdlInitializationException;
use Termeow\Infrastructure\Sdl\Rendering\SdlDisplayFactory;
use Termeow\Tests\Support\TestOptions;

final class SdlDisplayFailureTest extends TestCase
{
    public function testFailedFontInitializationDoesNotPreventLaterDisplay(): void
    {
        $settings = TestOptions::fromDefault();
        $screen = new LibvtermScreen(
            TerminalDimensions::of(1, 2),
            $settings->colors(),
            new LibvtermPaletteInstaller(),
            new LibvtermCellReaderFactory(),
        );
        $invalid = new TerminalOptions(
            FontSettings::of('/missing/font.ttf', 0, null, 0, 11, true),
            WindowSettings::titled('Invalid'),
            $settings->colors(),
        );
        try {
            (new SdlDisplayFactory())->create($screen, $invalid);
            self::fail('Expected font initialization to fail');
        } catch (SdlInitializationException $exception) {
            self::assertStringContainsString('TTF_OpenFontIndex', $exception->getMessage());
        }
        $display = (new SdlDisplayFactory())->create($screen, $settings);
        self::assertGreaterThan(0, $display->cellWidth());
        unset($display);
    }
}
