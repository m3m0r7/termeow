<?php

declare(strict_types=1);

namespace Termeow\Domain\Value;

interface WorkingDirectoryInterface
{
    public function path(): string;
}
