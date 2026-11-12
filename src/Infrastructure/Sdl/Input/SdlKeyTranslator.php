<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Input;

use Pnlx\Libsdl\Enums\SDL_EventType;
use Pnlx\Libsdl\Enums\SDL_KeyCode;
use Pnlx\Libsdl\Types\SDL_Event;

readonly class SdlKeyTranslator implements SdlKeyTranslatorInterface
{
    private const CTRL_MASK = 0x00c0;
    private const CONTROL_BYTE_MASK = 0xff;

    public function translate(SDL_Event $event): ?string
    {
        if ($event->getType() !== SDL_EventType::SDL_KEYDOWN->value) {
            return null;
        }
        $keysym = $event->getKey()->getKeysym();
        $key = $keysym->getSym();
        $modifiers = $keysym->getMod();
        if (($modifiers & self::CTRL_MASK) !== 0) {
            if ($key >= ord('a') && $key <= ord('z')) {
                return chr(($key - ord('a') + 1) & self::CONTROL_BYTE_MASK);
            }
            $control = match ($key) {
                SDL_KeyCode::SDLK_SPACE->value, ord('2'), ord('@') => "\x00",
                SDL_KeyCode::SDLK_LEFTBRACKET->value, ord('3') => "\x1b",
                SDL_KeyCode::SDLK_BACKSLASH->value, ord('4') => "\x1c",
                SDL_KeyCode::SDLK_RIGHTBRACKET->value, ord('5') => "\x1d",
                ord('6'), ord('^') => "\x1e",
                ord('7'), ord('_'), ord('-') => "\x1f",
                ord('8'), ord('?') => "\x7f",
                SDL_KeyCode::SDLK_LEFT->value => "\x1b[1;5D",
                SDL_KeyCode::SDLK_RIGHT->value => "\x1b[1;5C",
                SDL_KeyCode::SDLK_UP->value => "\x1b[1;5A",
                SDL_KeyCode::SDLK_DOWN->value => "\x1b[1;5B",
                default => null,
            };
            if ($control !== null) {
                return $control;
            }
        }
        return match ($key) {
            SDL_KeyCode::SDLK_RETURN->value => "\r",
            SDL_KeyCode::SDLK_BACKSPACE->value => "\x7f",
            SDL_KeyCode::SDLK_TAB->value => "\t",
            SDL_KeyCode::SDLK_ESCAPE->value => "\x1b",
            SDL_KeyCode::SDLK_UP->value => "\x1b[A",
            SDL_KeyCode::SDLK_DOWN->value => "\x1b[B",
            SDL_KeyCode::SDLK_RIGHT->value => "\x1b[C",
            SDL_KeyCode::SDLK_LEFT->value => "\x1b[D",
            SDL_KeyCode::SDLK_HOME->value => "\x1b[H",
            SDL_KeyCode::SDLK_END->value => "\x1b[F",
            SDL_KeyCode::SDLK_DELETE->value => "\x1b[3~",
            default => null,
        };
    }
}
