<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Input;

use Pnlx\Libsdl\Libsdl as SDL;
use Pnlx\Libsdl\Types\SDL_Event;
use Termeow\Application\Port\InputSource;

final class SdlInputSource implements InputSource
{
    private SDL_Event $event;
    private SdlInputEventHandler $handler;

    public function __construct()
    {
        $this->event = new SDL_Event();
        $this->handler = new SdlInputEventHandler();
    }

    public function poll(): iterable
    {
        while (SDL::SDL_PollEvent($this->event) !== 0) {
            yield from $this->handler->handle($this->event);
        }
    }
}
