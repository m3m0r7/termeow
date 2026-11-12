<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Cli;

interface CreditsInterface
{
    /** @return list<string> */
    public function lines(): array;
}
