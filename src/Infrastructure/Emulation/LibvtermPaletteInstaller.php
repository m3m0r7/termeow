<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Emulation;

use Pnlx\Libvterm\Libvterm as VTerm;
use Pnlx\Libvterm\Types\VTermColor;
use Pnlx\Libvterm\Types\VTermState;
use Termeow\Domain\Terminal\ColorSchemeInterface;

readonly class LibvtermPaletteInstaller implements LibvtermPaletteInstallerInterface
{
    public function install(VTermState $state, ColorSchemeInterface $colors): void
    {
        $paletteColor = new VTermColor();
        $paletteColor->setType(VTermColorFlag::RGB->value);
        foreach ($colors->palette() as $index => $color) {
            $paletteColor->cdata()[0]->rgb->red = $color->red();
            $paletteColor->cdata()[0]->rgb->green = $color->green();
            $paletteColor->cdata()[0]->rgb->blue = $color->blue();
            VTerm::vterm_state_set_palette_color($state, $index, $paletteColor);
        }
    }
}
