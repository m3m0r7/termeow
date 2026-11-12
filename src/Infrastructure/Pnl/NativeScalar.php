<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Pnl;

use Pnlx\Types\AnySizeInteger;

final class NativeScalar
{
    public static function int(int|AnySizeInteger $value): int
    {
        return is_int($value) ? $value : $value->toInt();
    }
}
