<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Input;

use Pnlx\Libsdl\Types\SDL_Event;
use Pnlx\Libsdl\Enums\SDL_EventType;
use Pnlx\Libsdl\Enums\SDL_WindowEventID;
use Termeow\Application\InputAction;
use Termeow\Application\TextComposition;

class SdlInputEventHandler implements SdlInputEventHandlerInterface
{
    private bool $composing = false;

    public function __construct(
        private readonly SdlKeyTranslatorInterface $keys,
        private readonly SdlTextDecoderInterface $text,
    ) {
    }

    public function setComposing(bool $composing): void
    {
        $this->composing = $composing;
    }


    /** @return iterable<InputAction> */
    public function handle(SDL_Event $event): iterable
    {
        $type = $event->getType();
        if ($type === SDL_EventType::SDL_QUIT->value) {
            yield InputAction::quit();
            return;
        }
        if (
            $type === SDL_EventType::SDL_WINDOWEVENT->value
            && $event->getWindow()->getEvent() === SDL_WindowEventID::SDL_WINDOWEVENT_FOCUS_LOST->value
        ) {
            if ($this->composing) {
                $this->setComposing(false);
                yield InputAction::composition(TextComposition::empty());
            }
            return;
        }
        if ($type === SDL_EventType::SDL_TEXTEDITING->value || $type === SDL_EventType::SDL_TEXTEDITING_EXT->value) {
            $composition = $this->text->composition($event);
            if ($composition !== null) {
                $this->setComposing($composition->text() !== '');
                yield InputAction::composition($composition);
            }
            return;
        }
        if ($type === SDL_EventType::SDL_TEXTINPUT->value) {
            if ($this->composing) {
                $this->setComposing(false);
                yield InputAction::composition(TextComposition::empty());
            }
            $text = $this->text->committed($event);
            if ($text !== null && $text !== '') {
                yield InputAction::write($text);
            }
            return;
        }
        if ($type === SDL_EventType::SDL_KEYDOWN->value && !$this->composing) {
            $bytes = $this->keys->translate($event);
            if ($bytes !== null && $bytes !== '') {
                yield InputAction::write($bytes);
            }
        }
    }
}
