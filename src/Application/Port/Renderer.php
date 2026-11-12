<?php

declare(strict_types=1);

namespace Termeow\Application\Port;

use Termeow\Application\TextComposition;

interface Renderer
{
    public function setComposition(TextComposition $composition): void;

    public function draw(): void;
}
