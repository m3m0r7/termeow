<?php

declare(strict_types=1);

namespace Termeow\Application\Port;

use Termeow\Domain\Configuration\TerminalSettingsInterface;

interface RendererFactoryInterface
{
    public function create(TerminalViewInterface $screen, TerminalSettingsInterface $settings): RendererInterface;
}
