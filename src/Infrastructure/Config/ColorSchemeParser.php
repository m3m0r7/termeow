<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Config;

use Termeow\Domain\Terminal\ColorScheme;
use Termeow\Domain\Terminal\RgbColor;

final class ColorSchemeParser
{
    private const NAMES = ['black', 'red', 'green', 'yellow', 'blue', 'magenta', 'cyan', 'white'];

    public static function fromConfig(mixed $config): ColorScheme
    {
        if (!is_array($config)) {
            throw new \InvalidArgumentException('colors must be a table');
        }
        foreach (['primary', 'normal', 'bright'] as $section) {
            if (!isset($config[$section]) || !is_array($config[$section])) {
                throw new \InvalidArgumentException("colors.{$section} must be a table");
            }
        }
        $ansi = [];
        foreach (['normal', 'bright'] as $section) {
            $values = $config[$section];
            if (!is_array($values)) {
                throw new \InvalidArgumentException("colors.{$section} must be a table");
            }
            foreach (self::NAMES as $name) {
                $ansi[] = self::parse($values[$name] ?? null, "colors.{$section}.{$name}");
            }
        }
        return new ColorScheme(
            self::parse($config['primary']['foreground'] ?? null, 'colors.primary.foreground'),
            self::parse($config['primary']['background'] ?? null, 'colors.primary.background'),
            $ansi,
        );
    }

    private static function parse(mixed $value, string $name): RgbColor
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException("{$name} must be a #RRGGBB color");
        }
        try {
            return RgbColor::fromHex($value);
        } catch (\InvalidArgumentException $exception) {
            throw new \InvalidArgumentException("{$name} must be a #RRGGBB color", 0, $exception);
        }
    }
}
