<?php

declare(strict_types=1);

namespace Termeow\Application\Port;

use Termeow\Domain\Terminal\Cell;
use Termeow\Domain\Terminal\Cursor;

interface ScreenInterface extends TerminalViewInterface, TerminalOutputWriterInterface, TerminalResponseReaderInterface
{
}
