<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Process\Port;

use Termeow\Application\Port\SessionLifecycleInterface;

interface PtyProcessInterface extends PtyPipeProviderInterface, SessionLifecycleInterface
{
}
