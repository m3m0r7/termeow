<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Emulation;

enum VTermColorFlag: int
{
    case RGB = 0;
    case DEFAULT_FOREGROUND = 2;
    case DEFAULT_BACKGROUND = 4;
}
