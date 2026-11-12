<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Cli;

interface VersionInfoInterface
{
    public function text(): string;
}
