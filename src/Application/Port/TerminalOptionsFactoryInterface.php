<?php

declare(strict_types=1);

namespace Termeow\Application\Port;

use Termeow\Domain\Configuration\TerminalSettingsInterface;

interface TerminalOptionsFactoryInterface
{
    public function create(LoadedConfigurationInterface $configuration): TerminalSettingsInterface;
}
