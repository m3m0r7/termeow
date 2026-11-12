<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Rendering;

use Termeow\Application\TextCompositionInterface;

interface CompositionPainterInterface
{
    public function draw(TextCompositionInterface $composition): void;

    /** @return array{int, int} */
    public function cursor(TextCompositionInterface $composition): array;
}
