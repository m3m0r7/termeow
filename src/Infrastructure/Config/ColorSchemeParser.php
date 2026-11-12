<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Config;

use Termeow\Infrastructure\Config\Exception\ConfigurationValueException;
use Termeow\Domain\Terminal\AnsiPalette;
use Termeow\Domain\Terminal\ColorScheme;
use Termeow\Domain\Terminal\RgbColor;

readonly class ColorSchemeParser implements ColorSchemeParserInterface
{
    private const NAMES = ['black', 'red', 'green', 'yellow', 'blue', 'magenta', 'cyan', 'white'];

    public function fromConfig(mixed $config): ColorScheme
    {
        if (!is_array($config)) {
            throw new ConfigurationValueException('colors must be a table');
        }
        foreach (['primary', 'normal', 'bright'] as $section) {
            if (!isset($config[$section]) || !is_array($config[$section])) {
                throw new ConfigurationValueException("colors.{$section} must be a table");
            }
        }
        $ansi = [];
        foreach (['normal', 'bright'] as $section) {
            $values = $config[$section];
            if (!is_array($values)) {
                throw new ConfigurationValueException("colors.{$section} must be a table");
            }
            foreach (self::NAMES as $name) {
                $ansi[] = $this->parse($values[$name] ?? null, "colors.{$section}.{$name}");
            }
        }
        return new ColorScheme(
            $this->parse($config['primary']['foreground'] ?? null, 'colors.primary.foreground'),
            $this->parse($config['primary']['background'] ?? null, 'colors.primary.background'),
            AnsiPalette::fromList($ansi),
        );
    }

    private function parse(mixed $value, string $name): RgbColor
    {
        if (!is_string($value)) {
            throw new ConfigurationValueException("{$name} must be a #RRGGBB color");
        }
        try {
            return RgbColor::fromHex($value);
        } catch (\InvalidArgumentException $exception) {
            throw new ConfigurationValueException("{$name} must be a #RRGGBB color", 0, $exception);
        }
    }
}
