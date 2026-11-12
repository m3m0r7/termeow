<?php

declare(strict_types=1);

namespace Termeow\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Termeow\Domain\Terminal\AnsiPalette;
use Termeow\Domain\Terminal\ColorScheme;
use Termeow\Domain\Terminal\RgbColor;
use Termeow\Infrastructure\Config\ColorSchemeParserInterface;
use Termeow\Infrastructure\Config\FontResolverInterface;
use Termeow\Infrastructure\Config\LoadedConfiguration;
use Termeow\Infrastructure\Config\TerminalOptionsFactory;
use Termeow\Infrastructure\Config\WindowSettings;
use Termeow\Infrastructure\Config\Exception\ConfigurationValueException;

final class TerminalOptionsFactoryTest extends TestCase
{
    public function testFontResolutionAndDefaultsAreInjected(): void
    {
        $font = $this->createMock(FontResolverInterface::class);
        $font->expects(self::once())->method('resolve')->with('Demo Font', '/home/demo')
            ->willReturn(['/installed/demo.ttf', 2]);
        $color = RgbColor::of(1, 2, 3);
        $scheme = new ColorScheme($color, $color, AnsiPalette::fromList(array_fill(0, 16, $color)));
        $colors = $this->createMock(ColorSchemeParserInterface::class);
        $colors->expects(self::once())->method('fromConfig')->with(null)->willReturn($scheme);
        $settings = (new TerminalOptionsFactory($font, $colors, WindowSettings::titled('Default')))->create(
            LoadedConfiguration::of(
                ['font' => ['family' => 'Demo Font', 'size' => 12]],
                ['font.family' => '/home/demo'],
            ),
        );
        self::assertSame('/installed/demo.ttf', $settings->fontPath());
        self::assertSame(2, $settings->fontIndex());
        self::assertSame(12, $settings->fontSize());
        self::assertSame('Default', $settings->windowTitle());
        self::assertSame($scheme, $settings->colors());
    }

    public function testInvalidFontSizeIsAConfigurationFailure(): void
    {
        $font = $this->createMock(FontResolverInterface::class);
        $font->expects(self::never())->method('resolve');
        $colors = $this->createMock(ColorSchemeParserInterface::class);
        $colors->expects(self::never())->method('fromConfig');
        $factory = new TerminalOptionsFactory($font, $colors, WindowSettings::titled('Default'));
        $this->expectException(ConfigurationValueException::class);
        $factory->create(LoadedConfiguration::of(
            ['font' => ['family' => 'Demo Font', 'size' => 4]],
            ['font.family' => '/home/demo'],
        ));
    }
}
