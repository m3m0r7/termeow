<?php

declare(strict_types=1);

namespace Termeow\Application\Port;

interface ConfigurationLoaderInterface
{
    public function load(): LoadedConfigurationInterface;
}
