<?php

declare(strict_types=1);

namespace Termeow\Tests\Support;

use Termeow\Domain\Value\WorkingDirectory;
use Termeow\Infrastructure\Config\ColorSchemeParser;
use Termeow\Infrastructure\Config\ConfigurationPaths;
use Termeow\Infrastructure\Config\FontResolver;
use Termeow\Infrastructure\Config\FontMatchCommand;
use Termeow\Infrastructure\Config\WindowSettings;
use Termeow\Infrastructure\Config\LayeredTomlConfigurationLoader;
use Termeow\Domain\Configuration\TerminalSettingsInterface;
use Termeow\Infrastructure\Config\TerminalOptionsFactory;

final class TestOptions
{
    public static function fromDefault(): TerminalSettingsInterface
    {
        $package = WorkingDirectory::at(dirname(__DIR__, 2));
        $configuration = (new LayeredTomlConfigurationLoader(
            new ConfigurationPaths($package, $package, $package),
        ))->load();
        return (new TerminalOptionsFactory(
            new FontResolver(new FontMatchCommand()),
            new ColorSchemeParser(),
            WindowSettings::titled('Termeow'),
        ))->create($configuration);
    }
}
