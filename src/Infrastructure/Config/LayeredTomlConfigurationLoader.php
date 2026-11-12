<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Config;

use Termeow\Application\Port\ConfigurationLoaderInterface;
use Termeow\Application\Port\LoadedConfigurationInterface;
use Termeow\Infrastructure\Config\Exception\ConfigurationReadException;
use Termeow\Infrastructure\Config\Exception\ConfigurationParseException;
use Termeow\Infrastructure\Config\Exception\ConfigurationValueException;
use Internal\Toml\Toml;

readonly class LayeredTomlConfigurationLoader implements ConfigurationLoaderInterface
{
    public function __construct(private ConfigurationPathsInterface $paths)
    {
    }

    public function load(): LoadedConfigurationInterface
    {
        $values = [];
        $directories = [];
        foreach ($this->paths->paths() as $index => $path) {
            if ($index > 0 && !is_file($path)) {
                continue;
            }
            $source = @file_get_contents($path);
            if ($source === false) {
                throw new ConfigurationReadException("Could not read configuration: {$path}");
            }
            try {
                $layer = Toml::parseToArray($source);
            } catch (\Throwable $exception) {
                throw new ConfigurationParseException("Invalid TOML configuration: {$path}", 0, $exception);
            }
            $values = $this->stringKeys(array_replace_recursive($values, $this->stringKeys($layer)));
            foreach (['family', 'fallback'] as $name) {
                if (isset($layer['font']) && is_array($layer['font']) && array_key_exists($name, $layer['font'])) {
                    $directories["font.{$name}"] = dirname($path);
                }
            }
        }
        return LoadedConfiguration::of($values, $directories);
    }

    /** @param array<array-key, mixed> $table
     *  @return array<string, mixed>
     */
    private function stringKeys(array $table): array
    {
        $result = [];
        foreach ($table as $key => $value) {
            if (!is_string($key)) {
                throw new ConfigurationValueException('Configuration keys must be strings');
            }
            $result[$key] = $value;
        }
        return $result;
    }
}
