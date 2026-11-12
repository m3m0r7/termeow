<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Rendering;

use Pnlx\Libsdl\Libsdl as SDL;
use Pnlx\LibsdlTtf\LibsdlTtf as TTF;
use Pnlx\LibsdlTtf\Types\TTF_Font;
use Termeow\Domain\Configuration\TerminalSettingsInterface;
use Termeow\Infrastructure\Sdl\Exception\InvalidFontException;
use Termeow\Infrastructure\Sdl\Exception\SdlInitializationException;

readonly class SdlFontSet implements SdlFontSetInterface
{
    private const MAX_CHARACTER_CELLS = 2;
    private const MONOSPACE_REFERENCE = 'M';
    private const MONOSPACE_SAMPLES = ['i', 'W'];

    private TTF_Font $primary;
    private ?TTF_Font $fallback;
    private int $width;
    private int $height;
    private bool $useAntialias;

    public function __construct(private SdlRuntimeInterface $runtime, TerminalSettingsInterface $settings)
    {
        $primary = TTF::TTF_OpenFontIndex($settings->fontPath(), $settings->fontSize(), $settings->fontIndex())
            ?? throw new SdlInitializationException('TTF_OpenFontIndex: ' . SDL::SDL_GetError());
        $fallback = null;
        try {
            if ($settings->fallbackFontPath() !== null) {
                $fallback = TTF::TTF_OpenFontIndex(
                    $settings->fallbackFontPath(),
                    $settings->fontSize(),
                    $settings->fallbackFontIndex(),
                ) ?? throw new SdlInitializationException('TTF_OpenFontIndex fallback: ' . SDL::SDL_GetError());
            }
            [$width, $height] = $this->measure($primary);
            $this->primary = $primary;
            $this->fallback = $fallback;
            $this->width = $width;
            $this->height = $height;
            $this->useAntialias = $settings->fontAntialias();
        } catch (\Throwable $exception) {
            if ($fallback !== null) {
                TTF::TTF_CloseFont($fallback);
            }
            TTF::TTF_CloseFont($primary);
            throw $exception;
        }
    }

    /** @return array{int, int} */
    private function measure(TTF_Font $font): array
    {
        $width = 0;
        $height = 0;
        if (TTF::TTF_SizeUTF8($font, self::MONOSPACE_REFERENCE, $width, $height) !== 0) {
            throw new SdlInitializationException('TTF_SizeUTF8: ' . SDL::SDL_GetError());
        }
        if (!is_int($width) || !is_int($height)) {
            throw new SdlInitializationException('TTF_SizeUTF8 returned invalid dimensions');
        }
        foreach (self::MONOSPACE_SAMPLES as $sample) {
            $sampleWidth = 0;
            $sampleHeight = 0;
            if (TTF::TTF_SizeUTF8($font, $sample, $sampleWidth, $sampleHeight) !== 0 || $sampleWidth !== $width) {
                throw new InvalidFontException('font.family must have equal-width ASCII glyphs');
            }
        }
        return [$width, max($height, TTF::TTF_FontLineSkip($font))];
    }

    public function runtime(): SdlRuntimeInterface
    {
        return $this->runtime;
    }
    public function primaryFont(): TTF_Font
    {
        return $this->primary;
    }
    public function fallbackFont(): ?TTF_Font
    {
        return $this->fallback;
    }
    public function cellWidth(): int
    {
        return $this->width;
    }
    public function lineHeight(): int
    {
        return $this->height;
    }
    public function antialias(): bool
    {
        return $this->useAntialias;
    }
    public function characterCells(string $character): int
    {
        return max(1, min(self::MAX_CHARACTER_CELLS, mb_strwidth($character, 'UTF-8')));
    }

    public function __destruct()
    {
        if ($this->fallback !== null) {
            TTF::TTF_CloseFont($this->fallback);
        }
        TTF::TTF_CloseFont($this->primary);
    }
}
