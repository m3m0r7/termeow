<?php

declare(strict_types=1);

namespace Termeow\Application\Port;

interface SessionInterface extends PtyInputWriterInterface, PtyOutputReaderInterface, SessionLifecycleInterface
{
}
