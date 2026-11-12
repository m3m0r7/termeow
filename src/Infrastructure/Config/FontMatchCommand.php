<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Config;

readonly class FontMatchCommand implements FontMatchCommandInterface
{
    private const COMMAND = 'fc-match';
    private const FORMAT = "%{family}\n%{file}\n%{index}\n";

    public function arguments(string $family): array
    {
        return [self::COMMAND, '-f', self::FORMAT, $family];
    }
}
