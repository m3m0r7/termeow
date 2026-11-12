<?php

declare(strict_types=1);

namespace Termeow\Application\Port;

interface PtyInputWriterInterface
{
    public function write(string $input): void;
}
