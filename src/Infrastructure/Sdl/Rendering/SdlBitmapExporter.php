<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Rendering;

use Pnlx\Libsdl\Libsdl as SDL;
use Pnlx\Libsdl\LibsdlContext;
use Termeow\Infrastructure\Sdl\Exception\BitmapExportException;

use const Pnlx\Libsdl\SDL_PIXELFORMAT_ARGB8888;

readonly class SdlBitmapExporter implements BitmapExporterInterface
{
    private const COLOR_DEPTH_BITS = 32;
    public function __construct(private SdlGlyphContextInterface $context)
    {
    }

    public function saveBitmap(string $path): void
    {
        $width = 0;
        $height = 0;
        SDL::SDL_GetRendererOutputSize($this->context->nativeRenderer(), $width, $height);
        if (!is_int($width) || !is_int($height)) {
            throw new BitmapExportException('SDL_GetRendererOutputSize returned invalid dimensions');
        }
        $surface = SDL::SDL_CreateRGBSurfaceWithFormat(
            0,
            $width,
            $height,
            self::COLOR_DEPTH_BITS,
            SDL_PIXELFORMAT_ARGB8888,
        );
        if ($surface === null) {
            throw new BitmapExportException('SDL_CreateRGBSurfaceWithFormat: ' . SDL::SDL_GetError());
        }
        try {
            $pixels = $surface->getPixels();
            if (!$pixels instanceof \FFI\CData) {
                throw new BitmapExportException('SDL surface pixels are unavailable');
            }
            if (
                SDL::SDL_RenderReadPixels(
                    $this->context->nativeRenderer(),
                    null,
                    SDL_PIXELFORMAT_ARGB8888,
                    new LibsdlContext($pixels),
                    $surface->getPitch(),
                ) !== 0
            ) {
                throw new BitmapExportException('SDL_RenderReadPixels: ' . SDL::SDL_GetError());
            }
            $file = SDL::SDL_RWFromFile($path, 'wb');
            if ($file === null || SDL::SDL_SaveBMP_RW($surface, $file, 1) !== 0) {
                throw new BitmapExportException('SDL_SaveBMP_RW: ' . SDL::SDL_GetError());
            }
        } finally {
            SDL::SDL_FreeSurface($surface);
        }
    }
}
