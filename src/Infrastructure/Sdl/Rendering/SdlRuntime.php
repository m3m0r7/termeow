<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Rendering;

use Pnlx\Libsdl\Libsdl as SDL;
use Pnlx\LibsdlTtf\LibsdlTtf as TTF;
use Termeow\Infrastructure\Sdl\Exception\SdlInitializationException;

use const Pnlx\Libsdl\SDL_HINT_IME_SHOW_UI;
use const Pnlx\Libsdl\SDL_HINT_IME_SUPPORT_EXTENDED_TEXT;
use const Pnlx\Libsdl\SDL_INIT_VIDEO;

class SdlRuntime implements SdlRuntimeInterface
{
    private const ENABLED_HINT = '1';
    public function __construct()
    {
        SDL::SDL_SetHint(SDL_HINT_IME_SHOW_UI, self::ENABLED_HINT);
        SDL::SDL_SetHint(SDL_HINT_IME_SUPPORT_EXTENDED_TEXT, self::ENABLED_HINT);
        if (SDL::SDL_Init(SDL_INIT_VIDEO) !== 0) {
            throw new SdlInitializationException('SDL_Init: ' . SDL::SDL_GetError());
        }
        if (TTF::TTF_Init() !== 0) {
            SDL::SDL_Quit();
            throw new SdlInitializationException('TTF_Init: ' . SDL::SDL_GetError());
        }
    }

    public function startTextInput(): void
    {
        SDL::SDL_StartTextInput();
    }

    public function stopTextInput(): void
    {
        SDL::SDL_StopTextInput();
    }

    public function __destruct()
    {
        TTF::TTF_Quit();
        SDL::SDL_Quit();
    }
}
