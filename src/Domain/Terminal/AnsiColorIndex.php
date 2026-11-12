<?php

declare(strict_types=1);

namespace Termeow\Domain\Terminal;

enum AnsiColorIndex: int
{
    case BLACK = 0;
    case RED = 1;
    case GREEN = 2;
    case YELLOW = 3;
    case BLUE = 4;
    case MAGENTA = 5;
    case CYAN = 6;
    case WHITE = 7;
    case BRIGHT_BLACK = 8;
    case BRIGHT_RED = 9;
    case BRIGHT_GREEN = 10;
    case BRIGHT_YELLOW = 11;
    case BRIGHT_BLUE = 12;
    case BRIGHT_MAGENTA = 13;
    case BRIGHT_CYAN = 14;
    case BRIGHT_WHITE = 15;
}
