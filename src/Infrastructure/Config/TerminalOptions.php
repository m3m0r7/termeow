<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Config;

use Internal\Toml\Toml;
use Termeow\Domain\Terminal\ColorScheme;

final readonly class TerminalOptions
{
    private function __construct(
        public string $fontPath,
        public int $fontIndex,
        public ?string $fallbackFontPath,
        public int $fallbackFontIndex,
        public int $fontSize,
        public bool $fontAntialias,
        public string $windowTitle,
        public ColorScheme $colors,
    ) {
    }

    public static function fromFile(string $path): self
    {
        $source = @file_get_contents($path);
        if ($source === false) {
            throw new \RuntimeException("Could not read configuration: {$path}");
        }
        $config = Toml::parseToArray($source);
        if (!isset($config['font']) || !is_array($config['font'])) {
            throw new \InvalidArgumentException('Configuration needs a [font] table');
        }
        $font = $config['font'];
        $size = $font['size'] ?? null;
        if (!is_int($size) || $size < 8 || $size > 36) {
            throw new \InvalidArgumentException('font.size must be an integer from 8 to 36');
        }
        $antialias = $font['antialias'] ?? true;
        if (!is_bool($antialias)) {
            throw new \InvalidArgumentException('font.antialias must be a boolean');
        }
        $family = $font['family'] ?? null;
        if (!is_string($family) || trim($family) === '') {
            throw new \InvalidArgumentException('font.family must be a nonempty font name or path');
        }
        $fallback = $font['fallback'] ?? null;
        if ($fallback !== null && (!is_string($fallback) || trim($fallback) === '')) {
            throw new \InvalidArgumentException('font.fallback must be a nonempty font name or path');
        }
        $window = $config['window'] ?? [];
        if (!is_array($window)) {
            throw new \InvalidArgumentException('window must be a table');
        }
        $title = $window['title'] ?? 'Termeow';
        if (!is_string($title) || trim($title) === '') {
            throw new \InvalidArgumentException('window.title must be nonempty text');
        }
        $directory = dirname(realpath($path) ?: $path);
        [$fontPath, $fontIndex] = self::resolveFont($family, $directory);
        [$fallbackFontPath, $fallbackFontIndex] = $fallback === null
            ? [null, 0]
            : self::resolveFont($fallback, $directory);
        return new self(
            $fontPath,
            $fontIndex,
            $fallbackFontPath,
            $fallbackFontIndex,
            $size,
            $antialias,
            $title,
            ColorSchemeParser::fromConfig($config['colors'] ?? null),
        );
    }

    public static function usage(): string
    {
        return <<<'TEXT'
Usage: termeow [--help|-v|--version]

Edit termeow.toml to set fonts, colors, and the window title.

TEXT;
    }

    /** @return array{string, int} */
    private static function resolveFont(string $configured, string $directory): array
    {
        if (str_contains($configured, '/') || preg_match('/\.(ttf|ttc|otf)$/i', $configured)) {
            $path = str_starts_with($configured, '/') ? $configured : $directory . '/' . $configured;
            if (!is_file($path) || !is_readable($path)) {
                throw new \InvalidArgumentException("Font file is not readable: {$configured}");
            }
            $resolved = realpath($path);
            if ($resolved === false) {
                throw new \RuntimeException("Could not resolve font path: {$path}");
            }
            return [$resolved, 0];
        }

        $process = @proc_open(
            ['fc-match', '-f', "%{family}\n%{file}\n%{index}\n", $configured],
            [0 => ['file', '/dev/null', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
        );
        if (!is_resource($process)) {
            throw new \RuntimeException('Fontconfig is required for font family names; use a font file path');
        }
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        if (proc_close($process) !== 0) {
            throw new \RuntimeException('Could not resolve font family; use a font file path');
        }
        [$family, $path, $index] = array_pad(explode("\n", trim($output), 4), 3, '');
        $requested = mb_strtolower(str_replace([' ', '_', '-'], '', $configured));
        $matched = mb_strtolower(str_replace([' ', '_', '-'], '', $family));
        if ($configured !== 'monospace' && !str_contains($matched, $requested)) {
            throw new \InvalidArgumentException("Font family not found: {$configured}");
        }
        if (!is_file($path) || !is_readable($path)) {
            throw new \RuntimeException("Resolved font is not readable: {$path}");
        }
        if ($index !== '' && !ctype_digit($index)) {
            throw new \RuntimeException("Resolved font index is invalid: {$index}");
        }
        return [$path, $index === '' ? 0 : (int) $index];
    }
}
