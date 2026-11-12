<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Config;

use Termeow\Infrastructure\Config\Exception\FontResolutionException;
use Termeow\Infrastructure\Config\Exception\ConfigurationValueException;
use Symfony\Component\Process\Process;

readonly class FontResolver implements FontResolverInterface
{
    private const GENERIC_FAMILY = 'monospace';
    private const FONT_MATCH_FIELDS = 3;
    private const FONT_MATCH_SPLIT_LIMIT = 4;

    public function __construct(private FontMatchCommandInterface $command)
    {
    }

    public function resolve(string $configured, string $directory): array
    {
        if (str_contains($configured, '/') || preg_match('/\.(ttf|ttc|otf)$/i', $configured) === 1) {
            $path = str_starts_with($configured, '/') ? $configured : $directory . '/' . $configured;
            if (!is_file($path) || !is_readable($path)) {
                throw new ConfigurationValueException("Font file is not readable: {$configured}");
            }
            $resolved = realpath($path);
            if ($resolved === false) {
                throw new FontResolutionException("Could not resolve font path: {$path}");
            }
            return [$resolved, 0];
        }
        $process = new Process($this->command->arguments($configured));
        try {
            $process->mustRun();
        } catch (\Throwable $exception) {
            throw new FontResolutionException('Could not resolve font family; use a font file path', 0, $exception);
        }
        [$family, $path, $index] = array_pad(
            explode("\n", trim($process->getOutput()), self::FONT_MATCH_SPLIT_LIMIT),
            self::FONT_MATCH_FIELDS,
            '',
        );
        $requested = mb_strtolower(str_replace([' ', '_', '-'], '', $configured));
        $matched = mb_strtolower(str_replace([' ', '_', '-'], '', $family));
        if ($configured !== self::GENERIC_FAMILY && !str_contains($matched, $requested)) {
            throw new ConfigurationValueException("Font family not found: {$configured}");
        }
        if (!is_file($path) || !is_readable($path)) {
            throw new FontResolutionException("Resolved font is not readable: {$path}");
        }
        if ($index !== '' && !ctype_digit($index)) {
            throw new FontResolutionException("Resolved font index is invalid: {$index}");
        }
        return [$path, $index === '' ? 0 : (int) $index];
    }
}
