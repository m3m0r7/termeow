<?php

declare(strict_types=1);

namespace Termeow\Application\Port;

interface PtyOutputReaderInterface
{
    public function read(): string;
}
