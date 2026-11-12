<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Rendering;

use Termeow\Application\Port\TerminalViewInterface;
use Termeow\Domain\Configuration\TerminalSettingsInterface;

interface SdlDisplayFactoryInterface
{
    public function create(TerminalViewInterface $screen, TerminalSettingsInterface $settings): SdlDisplayInterface;
}
