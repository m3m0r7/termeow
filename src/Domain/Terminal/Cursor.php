<?php

declare(strict_types=1);

namespace Termeow\Domain\Terminal;

readonly class Cursor implements CursorInterface
{
    private function __construct(public int $row, public int $column)
    {
    }

    public static function at(int $row, int $column): self
    {
        return new self($row, $column);
    }

    public function row(): int
    {
        return $this->row;
    }
    public function column(): int
    {
        return $this->column;
    }
}
