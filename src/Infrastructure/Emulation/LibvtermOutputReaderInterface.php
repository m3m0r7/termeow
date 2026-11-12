<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Emulation;

interface LibvtermOutputReaderInterface
{
    public function read(): string;
}
