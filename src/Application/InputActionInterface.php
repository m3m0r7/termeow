<?php

declare(strict_types=1);

namespace Termeow\Application;

interface InputActionInterface
{
    public function bytes(): string;

    public function quitRequested(): bool;

    public function compositionValue(): ?TextCompositionInterface;
}
