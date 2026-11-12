<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Rendering;

interface SdlRuntimeInterface
{
    public function startTextInput(): void;
    public function stopTextInput(): void;
}
