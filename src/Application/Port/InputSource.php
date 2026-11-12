<?php

declare(strict_types=1);

namespace Termeow\Application\Port;

use Termeow\Application\InputAction;

interface InputSource
{
    /** @return iterable<InputAction> */
    public function poll(): iterable;
}
