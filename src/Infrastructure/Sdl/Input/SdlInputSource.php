<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Input;

use Pnlx\Libsdl\Libsdl as SDL;
use Pnlx\Libsdl\Types\SDL_Event;
use Termeow\Application\Port\InputSourceInterface;

class SdlInputSource implements InputSourceInterface
{
    private SDL_Event $event;
    private readonly SdlInputEventHandlerInterface $handler;

    public function __construct(SdlInputEventHandlerInterface $handler)
    {
        $this->event = new SDL_Event();
        $this->handler = $handler;
    }

    public function poll(): iterable
    {
        while (SDL::SDL_PollEvent($this->event) !== 0) {
            yield from $this->handler->handle($this->event);
        }
    }
}
