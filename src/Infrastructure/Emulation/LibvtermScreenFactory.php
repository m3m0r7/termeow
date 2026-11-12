<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Emulation;

use Termeow\Application\Port\ScreenFactoryInterface;
use Termeow\Application\Port\ScreenInterface;
use Termeow\Domain\Terminal\ColorSchemeInterface;
use Termeow\Domain\Value\TerminalDimensionsInterface;

readonly class LibvtermScreenFactory implements ScreenFactoryInterface
{
    public function create(TerminalDimensionsInterface $dimensions, ColorSchemeInterface $colors): ScreenInterface
    {
        return new LibvtermScreen(
            $dimensions,
            $colors,
            new LibvtermPaletteInstaller(),
            new LibvtermCellReaderFactory(),
            new LibvtermOutputReaderFactory(),
        );
    }
}
