<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Rendering;

use Termeow\Application\Port\TerminalViewInterface;
use Termeow\Domain\Configuration\TerminalSettingsInterface;

readonly class SdlDisplayFactory implements SdlDisplayFactoryInterface
{
    public function create(TerminalViewInterface $screen, TerminalSettingsInterface $settings): SdlDisplayInterface
    {
        $runtime = new SdlRuntime();
        $fonts = new SdlFontSet($runtime, $settings);
        $window = new SdlWindow($fonts->runtime(), $fonts, $screen, $settings);
        return new SdlDisplay($fonts, $window);
    }
}
