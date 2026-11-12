<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Input;

use Pnlx\Libsdl\Types\SDL_Event;
use Termeow\Application\InputActionInterface;

interface SdlInputEventHandlerInterface
{
    /** @return iterable<InputActionInterface> */
    public function handle(SDL_Event $event): iterable;
}
