<?php

declare(strict_types=1);

namespace Termeow\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Termeow\Domain\Terminal\RgbColor;
use Termeow\Tests\Support\TestOptions;
use Termeow\Domain\Value\TerminalDimensions;

final class ColorSchemeTest extends TestCase
{
    public function testConfiguredAnsiPalette(): void
    {
        $options = TestOptions::fromDefault();
        self::assertCount(16, $options->colors()->palette());
        self::assertTrue($options->colors()->ansi(1)->equals(RgbColor::fromHex('#ff6188')));
        self::assertTrue($options->colors()->ansi(9)->equals(RgbColor::fromHex('#ff6188')));
    }

    public function testInvalidColorIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        RgbColor::fromHex('red');
    }
}
