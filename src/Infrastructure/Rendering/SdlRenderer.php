<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Rendering;

use Pnlx\Libsdl\Libsdl as SDL;
use Termeow\Application\Port\Renderer;
use Termeow\Application\Port\Screen;
use Termeow\Application\TextComposition;
use Termeow\Domain\Terminal\ColorScheme;
use Termeow\Domain\Terminal\RgbColor;
use Termeow\Infrastructure\Config\TerminalOptions;
use Termeow\Infrastructure\Pnl\NativeScalar;
use Pnlx\Libsdl\LibsdlContext;
use Pnlx\Libsdl\Types\SDL_Rect;
use Pnlx\LibsdlTtf\Types\TTF_Font;
use Pnlx\LibsdlTtf\LibsdlTtf as TTF;
use Pnlx\LibsdlTtf\Types\SDL_Color;

use const Pnlx\Libsdl\SDL_INIT_VIDEO;
use const Pnlx\Libsdl\SDL_HINT_IME_SHOW_UI;
use const Pnlx\Libsdl\SDL_HINT_IME_SUPPORT_EXTENDED_TEXT;
use const Pnlx\Libsdl\SDL_PIXELFORMAT_ARGB8888;
use const Pnlx\Libsdl\SDL_WINDOWPOS_CENTERED;
use const Pnlx\Libsdl\SDL_WINDOW_SHOWN;
use const Pnlx\LibsdlTtf\TTF_STYLE_BOLD;
use const Pnlx\LibsdlTtf\TTF_STYLE_ITALIC;

final class SdlRenderer implements Renderer
{
    private const PADDING = 8;
    private readonly ColorScheme $colors;
    private readonly bool $antialias;
    private readonly object $window;
    private readonly object $renderer;
    private readonly TTF_Font $font;
    private readonly ?TTF_Font $fallbackFont;
    private readonly int $cellWidth;
    private readonly int $lineHeight;
    private TextComposition $composition;

    public function __construct(private readonly Screen $screen, TerminalOptions $options)
    {
        $this->colors = $options->colors;
        $this->antialias = $options->fontAntialias;
        $this->composition = TextComposition::empty();
        SDL::SDL_SetHint(SDL_HINT_IME_SHOW_UI, '1');
        SDL::SDL_SetHint(SDL_HINT_IME_SUPPORT_EXTENDED_TEXT, '1');
        if (SDL::SDL_Init(SDL_INIT_VIDEO) !== 0) {
            throw new \RuntimeException('SDL_Init: ' . SDL::SDL_GetError());
        }
        if (TTF::TTF_Init() !== 0) {
            throw new \RuntimeException('TTF_Init: ' . SDL::SDL_GetError());
        }
        $this->font = TTF::TTF_OpenFontIndex($options->fontPath, $options->fontSize, $options->fontIndex)
            ?? throw new \RuntimeException('TTF_OpenFontIndex: ' . SDL::SDL_GetError());
        $this->fallbackFont = $options->fallbackFontPath === null ? null
            : (TTF::TTF_OpenFontIndex($options->fallbackFontPath, $options->fontSize, $options->fallbackFontIndex)
                ?? throw new \RuntimeException('TTF_OpenFontIndex fallback: ' . SDL::SDL_GetError()));
        $width = 0;
        $height = 0;
        if (TTF::TTF_SizeUTF8($this->font, 'M', $width, $height) !== 0) {
            throw new \RuntimeException('TTF_SizeUTF8: ' . SDL::SDL_GetError());
        }
        if (!is_int($width) || !is_int($height)) {
            throw new \RuntimeException('TTF_SizeUTF8 returned invalid dimensions');
        }
        foreach (['i', 'W'] as $sample) {
            $sampleWidth = 0;
            $sampleHeight = 0;
            if (TTF::TTF_SizeUTF8($this->font, $sample, $sampleWidth, $sampleHeight) !== 0 || $sampleWidth !== $width) {
                throw new \InvalidArgumentException('font.family must have equal-width ASCII glyphs');
            }
        }
        $this->cellWidth = $width;
        $this->lineHeight = max($height, TTF::TTF_FontLineSkip($this->font));
        $this->window = SDL::SDL_CreateWindow(
            $options->windowTitle,
            SDL_WINDOWPOS_CENTERED,
            SDL_WINDOWPOS_CENTERED,
            $screen->columns() * $this->cellWidth + 2 * self::PADDING,
            $screen->rows() * $this->lineHeight + 2 * self::PADDING,
            SDL_WINDOW_SHOWN,
        ) ?? throw new \RuntimeException('SDL_CreateWindow: ' . SDL::SDL_GetError());
        $this->renderer = SDL::SDL_CreateRenderer($this->window, -1, 0)
            ?? throw new \RuntimeException('SDL_CreateRenderer: ' . SDL::SDL_GetError());
        $this->positionTextInput();
        SDL::SDL_StartTextInput();
    }

    public function setComposition(TextComposition $composition): void
    {
        $this->composition = $composition;
    }

    public function draw(?string $bitmapPath = null): void
    {
        SDL::SDL_SetRenderDrawColor(
            $this->renderer,
            $this->colors->background->red,
            $this->colors->background->green,
            $this->colors->background->blue,
            255,
        );
        SDL::SDL_RenderClear($this->renderer);

        foreach ($this->screen->cells() as $glyph) {
            $foreground = $glyph->foreground ?? $this->colors->foreground;
            $background = $glyph->background ?? $this->colors->background;
            if ($glyph->reverse) {
                [$foreground, $background] = [$background, $foreground];
            }
            if (!$background->equals($this->colors->background)) {
                $this->fill(
                    self::PADDING + $glyph->column * $this->cellWidth,
                    self::PADDING + $glyph->row * $this->lineHeight,
                    $glyph->width * $this->cellWidth,
                    $this->lineHeight,
                    $background,
                );
            }
            if ($glyph->text === ' ') {
                continue;
            }
            $this->drawText(
                $glyph->text,
                self::PADDING + $glyph->column * $this->cellWidth,
                self::PADDING + $glyph->row * $this->lineHeight,
                $glyph->width * $this->cellWidth,
                $this->fontFor($glyph->text),
                $foreground,
                $glyph->bold,
                $glyph->italic,
            );
        }

        $cursor = $this->screen->cursor();
        $cursorRow = $cursor->row;
        $cursorColumn = $cursor->column;
        if (
            $this->composition->text === ''
            &&
            $cursorRow >= 0 && $cursorRow < $this->screen->rows()
            && $cursorColumn >= 0 && $cursorColumn < $this->screen->columns()
        ) {
            $this->fill(
                self::PADDING + $cursorColumn * $this->cellWidth,
                self::PADDING + ($cursorRow + 1) * $this->lineHeight - 3,
                $this->cellWidth,
                2,
                $this->colors->foreground,
            );
        }
        $this->drawComposition();
        $this->positionTextInput();
        if ($bitmapPath !== null) {
            $this->saveBitmap($bitmapPath);
        }
        SDL::SDL_RenderPresent($this->renderer);
    }

    private function drawComposition(): void
    {
        if ($this->composition->text === '') {
            return;
        }
        $cursor = $this->screen->cursor();
        $row = $cursor->row;
        $column = $cursor->column;
        foreach (mb_str_split($this->composition->text) as $index => $character) {
            $width = self::characterCells($character);
            if ($column + $width > $this->screen->columns()) {
                $row++;
                $column = 0;
            }
            if ($row >= $this->screen->rows()) {
                break;
            }
            $x = self::PADDING + $column * $this->cellWidth;
            $y = self::PADDING + $row * $this->lineHeight;
            $selected = $index >= $this->composition->cursor
                && $index < $this->composition->cursor + $this->composition->selectionLength;
            $this->fill(
                $x,
                $y,
                $width * $this->cellWidth,
                $this->lineHeight,
                $selected ? $this->colors->ansi[4] : $this->colors->background,
            );
            $this->drawText(
                $character,
                $x,
                $y,
                $width * $this->cellWidth,
                $this->fontFor($character),
                $this->colors->foreground,
                false,
                false,
            );
            $this->fill(
                $x,
                $y + $this->lineHeight - 2,
                $width * $this->cellWidth,
                1,
                $this->colors->ansi[6],
            );
            $column += $width;
        }
        [$caretRow, $caretColumn] = $this->compositionCursor();
        $this->fill(
            self::PADDING + $caretColumn * $this->cellWidth,
            self::PADDING + $caretRow * $this->lineHeight,
            1,
            $this->lineHeight - 2,
            $this->colors->foreground,
        );
    }

    /** @return array{int, int} */
    private function compositionCursor(): array
    {
        $cursor = $this->screen->cursor();
        $row = $cursor->row;
        $column = $cursor->column;
        foreach (mb_str_split(mb_substr($this->composition->text, 0, $this->composition->cursor)) as $character) {
            $width = self::characterCells($character);
            if ($column + $width > $this->screen->columns()) {
                $row++;
                $column = 0;
            }
            $column += $width;
        }
        if ($column >= $this->screen->columns()) {
            $row++;
            $column = 0;
        }
        return [min($row, $this->screen->rows() - 1), $column];
    }

    private static function characterCells(string $character): int
    {
        return max(1, min(2, mb_strwidth($character, 'UTF-8')));
    }

    private function positionTextInput(): void
    {
        [$row, $column] = $this->compositionCursor();
        $rect = (new SDL_Rect())
            ->setX(self::PADDING + $column * $this->cellWidth)
            ->setY(self::PADDING + $row * $this->lineHeight)
            ->setW($this->cellWidth)
            ->setH($this->lineHeight);
        SDL::SDL_SetTextInputRect($rect);
    }

    private function fontFor(string $text): TTF_Font
    {
        if ($this->fallbackFont !== null && TTF::TTF_GlyphIsProvided32($this->font, mb_ord($text, 'UTF-8')) === 0) {
            return $this->fallbackFont;
        }
        return $this->font;
    }

    private function drawText(
        string $text,
        int $x,
        int $y,
        int $width,
        TTF_Font $font,
        RgbColor $color,
        bool $bold,
        bool $italic,
    ): void {
        $foreground = (new SDL_Color())->setR($color->red)->setG($color->green)->setB($color->blue)->setA(255);
        $style = ($bold ? NativeScalar::int(TTF_STYLE_BOLD) : 0)
            | ($italic ? NativeScalar::int(TTF_STYLE_ITALIC) : 0);
        if ($style !== 0) {
            TTF::TTF_SetFontStyle($font, $style);
        }
        try {
            $surface = $this->antialias
                ? TTF::TTF_RenderUTF8_Blended($font, $text, $foreground)
                : TTF::TTF_RenderUTF8_Solid($font, $text, $foreground);
            if ($surface === null) {
                throw new \RuntimeException('TTF_RenderUTF8: ' . SDL::SDL_GetError());
            }
            try {
                $texture = SDL::SDL_CreateTextureFromSurface($this->renderer, $surface);
                if ($texture === null) {
                    throw new \RuntimeException('SDL_CreateTextureFromSurface: ' . SDL::SDL_GetError());
                }
                try {
                    $naturalWidth = $surface->getW();
                    $renderWidth = min($naturalWidth, $width);
                    $source = (new SDL_Rect())
                        ->setX(intdiv($naturalWidth - $renderWidth, 2))
                        ->setY(0)->setW($renderWidth)->setH($surface->getH());
                    $destination = (new SDL_Rect())
                        ->setX($x + intdiv($width - $renderWidth, 2))
                        ->setY($y + intdiv($this->lineHeight - $surface->getH(), 2))
                        ->setW($renderWidth)
                        ->setH($surface->getH());
                    if (SDL::SDL_RenderCopy($this->renderer, $texture, $source, $destination) !== 0) {
                        throw new \RuntimeException('SDL_RenderCopy: ' . SDL::SDL_GetError());
                    }
                } finally {
                    SDL::SDL_DestroyTexture($texture);
                }
            } finally {
                SDL::SDL_FreeSurface($surface);
            }
        } finally {
            if ($style !== 0) {
                TTF::TTF_SetFontStyle($font, 0);
            }
        }
    }

    private function fill(int $x, int $y, int $width, int $height, RgbColor $color): void
    {
        SDL::SDL_SetRenderDrawColor($this->renderer, $color->red, $color->green, $color->blue, 255);
        $rect = (new SDL_Rect())->setX($x)->setY($y)->setW($width)->setH($height);
        SDL::SDL_RenderFillRect($this->renderer, $rect);
    }

    private function saveBitmap(string $path): void
    {
        $width = 0;
        $height = 0;
        SDL::SDL_GetRendererOutputSize($this->renderer, $width, $height);
        if (!is_int($width) || !is_int($height)) {
            throw new \RuntimeException('SDL_GetRendererOutputSize returned invalid dimensions');
        }
        $surface = SDL::SDL_CreateRGBSurfaceWithFormat(0, $width, $height, 32, SDL_PIXELFORMAT_ARGB8888);
        if ($surface === null) {
            throw new \RuntimeException('SDL_CreateRGBSurfaceWithFormat: ' . SDL::SDL_GetError());
        }
        try {
            $pixels = $surface->getPixels();
            if (!$pixels instanceof \FFI\CData) {
                throw new \RuntimeException('SDL surface pixels are unavailable');
            }
            if (
                SDL::SDL_RenderReadPixels(
                    $this->renderer,
                    null,
                    SDL_PIXELFORMAT_ARGB8888,
                    new LibsdlContext($pixels),
                    $surface->getPitch(),
                ) !== 0
            ) {
                throw new \RuntimeException('SDL_RenderReadPixels: ' . SDL::SDL_GetError());
            }
            $file = SDL::SDL_RWFromFile($path, 'wb');
            if ($file === null || SDL::SDL_SaveBMP_RW($surface, $file, 1) !== 0) {
                throw new \RuntimeException('SDL_SaveBMP_RW: ' . SDL::SDL_GetError());
            }
        } finally {
            SDL::SDL_FreeSurface($surface);
        }
    }

    public function __destruct()
    {
        SDL::SDL_StopTextInput();
        SDL::SDL_DestroyRenderer($this->renderer);
        SDL::SDL_DestroyWindow($this->window);
        if ($this->fallbackFont !== null) {
            TTF::TTF_CloseFont($this->fallbackFont);
        }
        TTF::TTF_CloseFont($this->font);
        TTF::TTF_Quit();
        SDL::SDL_Quit();
    }
}
