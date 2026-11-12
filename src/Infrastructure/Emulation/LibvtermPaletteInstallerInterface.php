<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Emulation;

use Pnlx\Libvterm\Types\VTermState;
use Termeow\Domain\Terminal\ColorSchemeInterface;

interface LibvtermPaletteInstallerInterface
{
    public function install(VTermState $state, ColorSchemeInterface $colors): void;
}
