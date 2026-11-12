<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Input;

use Pnlx\Libsdl\Types\SDL_Event;
use Termeow\Application\TextCompositionInterface;

interface SdlTextDecoderInterface
{
    public function committed(SDL_Event $event): ?string;
    public function composition(SDL_Event $event): ?TextCompositionInterface;
}
