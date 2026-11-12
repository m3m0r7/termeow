<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Config;

interface FontResolverInterface
{
    /** @return array{string, int} */
    public function resolve(string $configured, string $directory): array;
}
