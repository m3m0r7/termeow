<?php

declare(strict_types=1);

namespace Termeow\Domain\Value;

interface TerminalDimensionsInterface
{
    public function rows(): int;

    public function columns(): int;
}
