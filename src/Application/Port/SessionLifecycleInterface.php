<?php

declare(strict_types=1);

namespace Termeow\Application\Port;

interface SessionLifecycleInterface
{
    public function running(): bool;

    public function close(): void;
}
