<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Input;

use Pnlx\Libsdl\Enums\SDL_EventType;
use Pnlx\Libsdl\Enums\SDL_KeyCode;
use Pnlx\Libsdl\Types\SDL_Event;

readonly class SdlKeyTranslator implements SdlKeyTranslatorInterface
{
    private const CTRL_MASK = 0x00c0;
    private const SHIFT_MASK = 0x0003;
    private const ALTGR_MASK = 0x4200;

    public function translate(SDL_Event $event): ?string
    {
        if ($event->getType() !== SDL_EventType::SDL_KEYDOWN->value) {
            return null;
        }
        $keysym = $event->getKey()->getKeysym();
        $key = $keysym->getSym();
        $modifiers = $keysym->getMod();
        if (($modifiers & self::CTRL_MASK) !== 0) {
            if (($modifiers & self::ALTGR_MASK) !== 0) {
                return null;
            }
            if ($key >= SDL_KeyCode::SDLK_a->value && $key <= SDL_KeyCode::SDLK_z->value) {
                return chr($key - SDL_KeyCode::SDLK_a->value + 1);
            }
            $control = match ($key) {
                SDL_KeyCode::SDLK_SPACE->value, SDL_KeyCode::SDLK_2->value, SDL_KeyCode::SDLK_AT->value => "\x00",
                SDL_KeyCode::SDLK_LEFTBRACKET->value, SDL_KeyCode::SDLK_3->value => "\x1b",
                SDL_KeyCode::SDLK_BACKSLASH->value, SDL_KeyCode::SDLK_4->value => "\x1c",
                SDL_KeyCode::SDLK_RIGHTBRACKET->value, SDL_KeyCode::SDLK_5->value => "\x1d",
                SDL_KeyCode::SDLK_6->value, SDL_KeyCode::SDLK_CARET->value => "\x1e",
                SDL_KeyCode::SDLK_7->value, SDL_KeyCode::SDLK_UNDERSCORE->value,
                    SDL_KeyCode::SDLK_MINUS->value => "\x1f",
                SDL_KeyCode::SDLK_8->value, SDL_KeyCode::SDLK_QUESTION->value => "\x7f",
                SDL_KeyCode::SDLK_SLASH->value => ($modifiers & self::SHIFT_MASK) !== 0 ? "\x7f" : null,
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
