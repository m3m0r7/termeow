<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Input;

use Pnlx\Libsdl\Types\SDL_Event;

interface SdlKeyTranslatorInterface
{
    public function translate(SDL_Event $event): ?string;
}
