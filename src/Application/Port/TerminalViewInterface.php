<?php

declare(strict_types=1);

namespace Termeow\Application\Port;

use Termeow\Domain\Terminal\CellInterface;
use Termeow\Domain\Terminal\CursorInterface;

interface TerminalViewInterface
{
    public function rows(): int;

    public function columns(): int;

    /** @return iterable<CellInterface> */
    public function cells(): iterable;

    public function cursor(): CursorInterface;
}
