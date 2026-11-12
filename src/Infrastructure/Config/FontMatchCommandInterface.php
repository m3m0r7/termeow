<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Config;

interface FontMatchCommandInterface
{
    /** @return list<string> */
    public function arguments(string $family): array;
}
