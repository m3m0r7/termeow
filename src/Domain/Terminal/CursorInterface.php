<?php

declare(strict_types=1);

namespace Termeow\Domain\Terminal;

interface CursorInterface
{
    public function row(): int;

    public function column(): int;
}
