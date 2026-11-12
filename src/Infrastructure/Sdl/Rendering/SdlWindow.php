<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Rendering;

use Pnlx\Libsdl\Libsdl as SDL;
use Pnlx\Libsdl\Types\SDL_Rect;
use Pnlx\Libsdl\Types\SDL_Renderer;
use Pnlx\Libsdl\Types\SDL_Window as NativeWindow;
use Termeow\Application\Port\TerminalViewInterface;
use Termeow\Domain\Configuration\TerminalSettingsInterface;
use Termeow\Domain\Terminal\RgbColorInterface;
use Termeow\Infrastructure\Sdl\Exception\SdlInitializationException;

use const Pnlx\Libsdl\SDL_WINDOWPOS_CENTERED;
use const Pnlx\Libsdl\SDL_WINDOW_SHOWN;

class SdlWindow implements SdlWindowInterface
{
    private const PADDING = 8;
    private const OPAQUE_ALPHA = 255;
    private const DEFAULT_RENDER_DRIVER = -1;

    private readonly NativeWindow $window;
    private readonly SDL_Renderer $renderer;

    public function __construct(
        private readonly SdlRuntimeInterface $runtime,
        private readonly SdlFontSetInterface $fonts,
        TerminalViewInterface $screen,
        TerminalSettingsInterface $settings,
    ) {
        $window = SDL::SDL_CreateWindow(
            $settings->windowTitle(),
            SDL_WINDOWPOS_CENTERED,
            SDL_WINDOWPOS_CENTERED,
            $screen->columns() * $fonts->cellWidth() + 2 * self::PADDING,
            $screen->rows() * $fonts->lineHeight() + 2 * self::PADDING,
            SDL_WINDOW_SHOWN,
        ) ?? throw new SdlInitializationException('SDL_CreateWindow: ' . SDL::SDL_GetError());
        $renderer = null;
        try {
            $renderer = SDL::SDL_CreateRenderer($window, self::DEFAULT_RENDER_DRIVER, 0)
                ?? throw new SdlInitializationException('SDL_CreateRenderer: ' . SDL::SDL_GetError());
            $runtime->startTextInput();
            $this->renderer = $renderer;
            $this->window = $window;
        } catch (\Throwable $exception) {
            if ($renderer !== null) {
                SDL::SDL_DestroyRenderer($renderer);
            }
            SDL::SDL_DestroyWindow($window);
            throw $exception;
        }
    }

    public function nativeRenderer(): SDL_Renderer
    {
        return $this->renderer;
    }
    public function padding(): int
    {
        return self::PADDING;
    }

    public function clear(RgbColorInterface $color): void
    {
        $this->setDrawColor($color);
        SDL::SDL_RenderClear($this->renderer);
    }

    public function fill(int $x, int $y, int $width, int $height, RgbColorInterface $color): void
    {
        $this->setDrawColor($color);
        $rect = (new SDL_Rect())->setX($x)->setY($y)->setW($width)->setH($height);
        SDL::SDL_RenderFillRect($this->renderer, $rect);
    }

    private function setDrawColor(RgbColorInterface $color): void
    {
        SDL::SDL_SetRenderDrawColor(
            $this->renderer,
            $color->red(),
            $color->green(),
            $color->blue(),
            self::OPAQUE_ALPHA,
        );
    }

    public function positionTextInput(int $row, int $column): void
    {
        $rect = (new SDL_Rect())
            ->setX(self::PADDING + $column * $this->fonts->cellWidth())
            ->setY(self::PADDING + $row * $this->fonts->lineHeight())
            ->setW($this->fonts->cellWidth())
            ->setH($this->fonts->lineHeight());
        SDL::SDL_SetTextInputRect($rect);
    }

    public function present(): void
    {
        SDL::SDL_RenderPresent($this->renderer);
    }

    public function __destruct()
    {
        $this->runtime->stopTextInput();
        SDL::SDL_DestroyRenderer($this->renderer);
        SDL::SDL_DestroyWindow($this->window);
    }
}
