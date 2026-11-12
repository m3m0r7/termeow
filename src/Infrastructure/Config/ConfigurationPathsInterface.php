<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Config;

interface ConfigurationPathsInterface
{
    /** @return list<string> */
    public function paths(): array;
}
