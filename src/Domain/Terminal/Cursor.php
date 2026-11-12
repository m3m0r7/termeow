<?php

declare(strict_types=1);

namespace Termeow\Domain\Terminal;

final readonly class Cursor
{
    public function __construct(public int $row, public int $column)
    {
    }
}
