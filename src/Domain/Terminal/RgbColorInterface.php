<?php

declare(strict_types=1);

namespace Termeow\Domain\Terminal;

interface RgbColorInterface
{
    public function red(): int;

    public function green(): int;

    public function blue(): int;

    public function equals(RgbColorInterface $other): bool;
}
