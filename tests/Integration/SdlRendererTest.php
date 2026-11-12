<?php

declare(strict_types=1);

namespace Termeow\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Pnlx\LibsdlTtf\LibsdlTtf as TTF;
use Termeow\Application\TextComposition;
use Termeow\Tests\Support\TestOptions;
use Termeow\Domain\Value\TerminalDimensions;
use Termeow\Infrastructure\Emulation\LibvtermScreen;
use Termeow\Infrastructure\Emulation\LibvtermPaletteInstaller;
use Termeow\Infrastructure\Emulation\LibvtermCellReaderFactory;
use Termeow\Infrastructure\Emulation\LibvtermOutputReaderFactory;
use Termeow\Infrastructure\Sdl\Rendering\SdlRendererFactory;
use Termeow\Infrastructure\Sdl\Rendering\SdlDisplayFactory;

final class SdlRendererTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $directory = dirname(__DIR__, 2) . '/tmp';
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            self::fail('Could not create the bitmap output directory');
        }
    }

    public function testRenderedBitmapContainsAnsiForegroundAndBackground(): void
    {
        $options = TestOptions::fromDefault();
        $screen = new LibvtermScreen(
            TerminalDimensions::of(2, 30),
            $options->colors(),
            new LibvtermPaletteInstaller(),
            new LibvtermCellReaderFactory(),
            new LibvtermOutputReaderFactory(),
        );
        $screen->write("pwd \x1b[31mRED\x1b[0m\r\n\x1b[48;2;12;34;56m  \x1b[0m漢\u{E0B0}");
        $view = (new SdlRendererFactory(new SdlDisplayFactory()))->create($screen, $options);
        $path = dirname(__DIR__, 2) . '/tmp/phpunit-render.bmp';
        $view->draw($path);
        $bitmap = file_get_contents($path);
        self::assertIsString($bitmap);
        self::assertSame('BM', substr($bitmap, 0, 2));
        self::assertSame(32, $this->bitmapInteger($bitmap, 28, 2));
        $offset = $this->bitmapInteger($bitmap, 10, 4);
        $redPixels = 0;
        $backgroundPixels = 0;
        for ($index = $offset; $index + 3 < strlen($bitmap); $index += 4) {
            $blue = ord($bitmap[$index]);
            $green = ord($bitmap[$index + 1]);
            $red = ord($bitmap[$index + 2]);
            if ($red > 100 && $red > $green * 1.6 && $red > $blue * 1.3) {
                $redPixels++;
            }
            if ([$red, $green, $blue] === [12, 34, 56]) {
                $backgroundPixels++;
            }
        }
        self::assertGreaterThan(10, $redPixels);
        self::assertGreaterThan(10, $backgroundPixels);
        unset($view);
    }

    public function testDefaultTextIsBrightAndBoldGlyphIsHeavier(): void
    {
        $options = TestOptions::fromDefault();
        $screen = new LibvtermScreen(
            TerminalDimensions::of(1, 4),
            $options->colors(),
            new LibvtermPaletteInstaller(),
            new LibvtermCellReaderFactory(),
            new LibvtermOutputReaderFactory(),
        );
        $screen->write("B\x1b[1mB\x1b[0m");
        $view = (new SdlRendererFactory(new SdlDisplayFactory()))->create($screen, $options);
        $path = dirname(__DIR__, 2) . '/tmp/phpunit-brightness.bmp';
        $view->draw($path);
        $bitmap = file_get_contents($path);
        self::assertIsString($bitmap);
        $width = $this->bitmapInteger($bitmap, 18, 4);
        $height = $this->bitmapInteger($bitmap, 22, 4);
        $offset = $this->bitmapInteger($bitmap, 10, 4);
        $cellWidth = intdiv($width - 16, 4);
        $counts = [0, 0];
        $brightest = 0;
        for ($y = 8; $y < $height - 8; $y++) {
            for ($cell = 0; $cell < 2; $cell++) {
                for ($x = 8 + $cell * $cellWidth; $x < 8 + ($cell + 1) * $cellWidth; $x++) {
                    $red = $this->pixelRed($bitmap, $width, $height, $offset, $x, $y);
                    if ($red > 32) {
                        $counts[$cell]++;
                    }
                    $brightest = max($brightest, $red);
                }
            }
        }
        self::assertGreaterThan(200, $brightest);
        self::assertGreaterThan($counts[0], $counts[1]);
        unset($view);
    }

    public function testCjkGlyphIsDrawnAtNaturalWidth(): void
    {
        $options = TestOptions::fromDefault();
        $screen = new LibvtermScreen(
            TerminalDimensions::of(1, 4),
            $options->colors(),
            new LibvtermPaletteInstaller(),
            new LibvtermCellReaderFactory(),
            new LibvtermOutputReaderFactory(),
        );
        $screen->write('漢');
        $view = (new SdlRendererFactory(new SdlDisplayFactory()))->create($screen, $options);
        $path = dirname(__DIR__, 2) . '/tmp/phpunit-width.bmp';
        $view->draw($path);
        $bitmap = file_get_contents($path);
        self::assertIsString($bitmap);
        $width = $this->bitmapInteger($bitmap, 18, 4);
        $height = $this->bitmapInteger($bitmap, 22, 4);
        $offset = $this->bitmapInteger($bitmap, 10, 4);
        $cellWidth = intdiv($width - 16, 4);

        $font = TTF::TTF_OpenFontIndex($options->fontPath(), $options->fontSize(), $options->fontIndex());
        self::assertNotNull($font);
        if (TTF::TTF_GlyphIsProvided32($font, mb_ord('漢', 'UTF-8')) === 0 && $options->fallbackFontPath() !== null) {
            TTF::TTF_CloseFont($font);
            $font = TTF::TTF_OpenFontIndex(
                $options->fallbackFontPath(),
                $options->fontSize(),
                $options->fallbackFontIndex(),
            );
            self::assertNotNull($font);
        }
        $naturalWidth = 0;
        $naturalHeight = 0;
        self::assertSame(0, TTF::TTF_SizeUTF8($font, '漢', $naturalWidth, $naturalHeight));
        TTF::TTF_CloseFont($font);
        self::assertIsInt($naturalWidth);

        $lastPixel = -1;
        for ($y = 8; $y < $height - 8; $y++) {
            for ($x = 8; $x < 8 + 2 * $cellWidth; $x++) {
                if ($this->pixelRed($bitmap, $width, $height, $offset, $x, $y) > 0) {
                    $lastPixel = max($lastPixel, $x);
                }
            }
        }
        self::assertGreaterThanOrEqual(8, $lastPixel);
        $naturalEdge = 8 + intdiv(2 * $cellWidth - $naturalWidth, 2) + $naturalWidth;
        self::assertLessThan($naturalEdge, $lastPixel);
        unset($view);
    }

    public function testCompositionIsVisibleWithoutChangingTerminalOutput(): void
    {
        $options = TestOptions::fromDefault();
        $screen = new LibvtermScreen(
            TerminalDimensions::of(1, 20),
            $options->colors(),
            new LibvtermPaletteInstaller(),
            new LibvtermCellReaderFactory(),
            new LibvtermOutputReaderFactory(),
        );
        $screen->write('pwd ');
        $view = (new SdlRendererFactory(new SdlDisplayFactory()))->create($screen, $options);
        $before = dirname(__DIR__, 2) . '/tmp/phpunit-ime-before.bmp';
        $during = dirname(__DIR__, 2) . '/tmp/phpunit-ime-composing.bmp';
        $after = dirname(__DIR__, 2) . '/tmp/phpunit-ime-after.bmp';
        $view->draw($before);
        $view->setComposition(TextComposition::of('にほん', 3, 0));
        $view->draw($during);
        self::assertSame('pwd', $screen->rowText(0));
        self::assertNotSame(file_get_contents($before), file_get_contents($during));
        $view->setComposition(TextComposition::empty());
        $view->draw($after);
        self::assertSame(file_get_contents($before), file_get_contents($after));
        unset($view);
    }

    private function pixelRed(string $bitmap, int $width, int $height, int $offset, int $x, int $y): int
    {
        $index = $offset + (($height - 1 - $y) * $width + $x) * 4;
        return ord($bitmap[$index + 2]);
    }

    private function bitmapInteger(string $bitmap, int $offset, int $bytes): int
    {
        $format = $bytes === 2 ? 'vvalue' : 'Vvalue';
        $value = unpack($format, substr($bitmap, $offset, $bytes));
        if (!is_array($value) || !isset($value['value']) || !is_int($value['value'])) {
            throw new \RuntimeException('Invalid bitmap header');
        }
        return $value['value'];
    }
}
