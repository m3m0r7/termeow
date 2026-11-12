<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Config;

use Termeow\Application\Port\TerminalOptionsFactoryInterface;
use Termeow\Application\Port\LoadedConfigurationInterface;
use Termeow\Domain\Configuration\TerminalSettingsInterface;
use Termeow\Infrastructure\Config\Exception\ConfigurationValueException;

readonly class TerminalOptionsFactory implements TerminalOptionsFactoryInterface
{
    private const MIN_FONT_SIZE = 8;
    private const MAX_FONT_SIZE = 36;

    public function __construct(
        private FontResolverInterface $fonts,
        private ColorSchemeParserInterface $colors,
        private WindowSettingsInterface $defaultWindow,
    ) {
    }

    public function create(LoadedConfigurationInterface $configuration): TerminalSettingsInterface
    {
        $config = $configuration->values();
        if (!isset($config['font']) || !is_array($config['font'])) {
            throw new ConfigurationValueException('Configuration needs a [font] table');
        }
        $font = $config['font'];
        $size = $font['size'] ?? null;
        if (!is_int($size) || $size < self::MIN_FONT_SIZE || $size > self::MAX_FONT_SIZE) {
            throw new ConfigurationValueException('font.size must be an integer from 8 to 36');
        }
        $antialias = $font['antialias'] ?? true;
        if (!is_bool($antialias)) {
            throw new ConfigurationValueException('font.antialias must be a boolean');
        }
        $family = $font['family'] ?? null;
        if (!is_string($family) || trim($family) === '') {
            throw new ConfigurationValueException('font.family must be a nonempty font name or path');
        }
        $fallback = $font['fallback'] ?? null;
        if ($fallback !== null && (!is_string($fallback) || trim($fallback) === '')) {
            throw new ConfigurationValueException('font.fallback must be a nonempty font name or path');
        }
        $window = $config['window'] ?? [];
        if (!is_array($window)) {
            throw new ConfigurationValueException('window must be a table');
        }
        $title = $window['title'] ?? $this->defaultWindow->title();
        if (!is_string($title) || trim($title) === '') {
            throw new ConfigurationValueException('window.title must be nonempty text');
        }
        [$fontPath, $fontIndex] = $this->fonts->resolve($family, $configuration->sourceDirectory('font.family'));
        [$fallbackFontPath, $fallbackFontIndex] = $fallback === null ? [null, 0]
            : $this->fonts->resolve($fallback, $configuration->sourceDirectory('font.fallback'));
        return new TerminalOptions(
            FontSettings::of($fontPath, $fontIndex, $fallbackFontPath, $fallbackFontIndex, $size, $antialias),
            WindowSettings::titled($title),
            $this->colors->fromConfig($config['colors'] ?? null),
        );
    }
}
