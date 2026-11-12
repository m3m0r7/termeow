<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Input;

use Pnlx\Libsdl\Types\SDL_Event;
use Termeow\Application\InputAction;
use Termeow\Application\TextComposition;
use Termeow\Infrastructure\Pnl\NativeScalar;

use const Pnlx\Libsdl\SDL_KEYDOWN;
use const Pnlx\Libsdl\SDL_QUIT;
use const Pnlx\Libsdl\SDL_TEXTEDITING;
use const Pnlx\Libsdl\SDL_TEXTEDITING_EXT;
use const Pnlx\Libsdl\SDL_TEXTINPUT;
use const Pnlx\Libsdl\SDL_WINDOWEVENT;
use const Pnlx\Libsdl\SDL_WINDOWEVENT_FOCUS_LOST;

final class SdlInputEventHandler
{
    private bool $composing = false;

    /** @return iterable<InputAction> */
    public function handle(SDL_Event $event): iterable
    {
        $type = $event->getType();
        if ($type === NativeScalar::int(SDL_QUIT)) {
            yield InputAction::quit();
            return;
        }
        if (
            $type === NativeScalar::int(SDL_WINDOWEVENT)
            && $event->getWindow()->getEvent() === NativeScalar::int(SDL_WINDOWEVENT_FOCUS_LOST)
        ) {
            if ($this->composing) {
                $this->composing = false;
                yield InputAction::composition(TextComposition::empty());
            }
            return;
        }
        if ($type === NativeScalar::int(SDL_TEXTEDITING) || $type === NativeScalar::int(SDL_TEXTEDITING_EXT)) {
            $composition = SdlInputDecoder::compositionFromEvent($event);
            if ($composition !== null) {
                $this->composing = $composition->text !== '';
                yield InputAction::composition($composition);
            }
            return;
        }
        if ($type === NativeScalar::int(SDL_TEXTINPUT)) {
            if ($this->composing) {
                $this->composing = false;
                yield InputAction::composition(TextComposition::empty());
            }
            $text = SdlInputDecoder::fromEvent($event);
            if ($text !== null && $text !== '') {
                yield InputAction::write($text);
            }
            return;
        }
        if ($type === NativeScalar::int(SDL_KEYDOWN) && !$this->composing) {
            $bytes = SdlInputDecoder::fromEvent($event);
            if ($bytes !== null && $bytes !== '') {
                yield InputAction::write($bytes);
            }
        }
    }
}
