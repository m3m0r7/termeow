<?php

declare(strict_types=1);

namespace Termeow\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Termeow\Domain\Terminal\RgbColor;
use Termeow\Infrastructure\Config\TerminalOptions;

final class ColorSchemeTest extends TestCase
{
    public function testConfiguredAnsiPalette(): void
    {
        $options = TerminalOptions::fromFile(dirname(__DIR__, 2) . '/termeow.toml');
        self::assertCount(16, $options->colors->ansi);
        self::assertTrue($options->colors->ansi[1]->equals(RgbColor::fromHex('#ff6188')));
        self::assertTrue($options->colors->ansi[9]->equals(RgbColor::fromHex('#ff6188')));
    }

    public function testInvalidColorIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        RgbColor::fromHex('red');
    }
}
