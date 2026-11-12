<?php

declare(strict_types=1);

namespace Termeow\Application\Port;

interface Session
{
    public function write(string $input): void;

    public function read(): string;

    public function running(): bool;

    public function close(): void;
}
