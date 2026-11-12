<?php

declare(strict_types=1);

namespace Termeow\Application\Port;

use Termeow\Application\TextCompositionInterface;

interface RendererInterface
{
    public function setComposition(TextCompositionInterface $composition): void;

    public function draw(): void;
}
