<?php

declare(strict_types=1);

namespace Termeow\Application\Port;

use Termeow\Domain\Terminal\ColorSchemeInterface;
use Termeow\Domain\Value\TerminalDimensionsInterface;

interface ScreenFactoryInterface
{
    public function create(TerminalDimensionsInterface $dimensions, ColorSchemeInterface $colors): ScreenInterface;
}
