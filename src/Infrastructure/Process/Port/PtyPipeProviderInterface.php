<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Process\Port;

interface PtyPipeProviderInterface
{
    /** @return resource */
    public function inputPipe();

    /** @return list<resource> */
    public function outputPipes(): array;
}
