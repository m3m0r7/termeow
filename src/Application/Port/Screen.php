<?php

declare(strict_types=1);

namespace Termeow\Application\Port;

use Termeow\Domain\Terminal\Cell;
use Termeow\Domain\Terminal\Cursor;

interface Screen
{
    public function rows(): int;

    public function columns(): int;

    public function write(string $data): void;

    /** @return iterable<Cell> */
    public function cells(): iterable;

    public function cursor(): Cursor;
}
