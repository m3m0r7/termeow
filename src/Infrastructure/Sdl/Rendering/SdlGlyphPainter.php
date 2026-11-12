<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Rendering;

use Pnlx\Libsdl\Libsdl as SDL;
use Pnlx\LibsdlTtf\LibsdlTtf as TTF;
use Pnlx\LibsdlTtf\Types\TTF_Font;
use Pnlx\LibsdlTtf\Types\SDL_Color;
use Pnlx\Libsdl\Types\SDL_Rect;
use Termeow\Domain\Terminal\RgbColorInterface;
use Termeow\Infrastructure\Sdl\Exception\GlyphRenderingException;

readonly class SdlGlyphPainter implements GlyphPainterInterface
{
    private const OPAQUE_ALPHA = 255;
    public function __construct(private SdlGlyphContextInterface $context)
    {
    }

    private function fontFor(string $text): TTF_Font
    {
        if (
            $this->context->fallbackFont() !== null
            && TTF::TTF_GlyphIsProvided32($this->context->primaryFont(), mb_ord($text, 'UTF-8')) === 0
        ) {
            return $this->context->fallbackFont();
        }
        return $this->context->primaryFont();
    }

    public function drawText(
        string $text,
        int $x,
        int $y,
        int $width,
        RgbColorInterface $color,
        bool $bold,
        bool $italic,
    ): void {
        $font = $this->fontFor($text);
        $foreground = (new SDL_Color())
            ->setR($color->red())
            ->setG($color->green())
            ->setB($color->blue())
            ->setA(self::OPAQUE_ALPHA);
        $style = ($bold ? FontStyle::BOLD->value : 0)
            | ($italic ? FontStyle::ITALIC->value : 0);
        if ($style !== 0) {
            TTF::TTF_SetFontStyle($font, $style);
        }
        try {
            $surface = $this->context->antialias()
                ? TTF::TTF_RenderUTF8_Blended($font, $text, $foreground)
                : TTF::TTF_RenderUTF8_Solid($font, $text, $foreground);
            if ($surface === null) {
                throw new GlyphRenderingException('TTF_RenderUTF8: ' . SDL::SDL_GetError());
            }
            try {
                $texture = SDL::SDL_CreateTextureFromSurface($this->context->nativeRenderer(), $surface);
                if ($texture === null) {
                    throw new GlyphRenderingException('SDL_CreateTextureFromSurface: ' . SDL::SDL_GetError());
                }
                try {
                    $naturalWidth = $surface->getW();
                    $renderWidth = min($naturalWidth, $width);
                    $source = (new SDL_Rect())
                        ->setX(intdiv($naturalWidth - $renderWidth, 2))
                        ->setY(0)->setW($renderWidth)->setH($surface->getH());
                    $destination = (new SDL_Rect())
                        ->setX($x + intdiv($width - $renderWidth, 2))
                        ->setY($y + intdiv($this->context->lineHeight() - $surface->getH(), 2))
                        ->setW($renderWidth)
                        ->setH($surface->getH());
                    if (SDL::SDL_RenderCopy($this->context->nativeRenderer(), $texture, $source, $destination) !== 0) {
                        throw new GlyphRenderingException('SDL_RenderCopy: ' . SDL::SDL_GetError());
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
}
