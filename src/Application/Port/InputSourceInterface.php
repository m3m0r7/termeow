<?php

declare(strict_types=1);

namespace Termeow\Application\Port;

use Termeow\Application\InputActionInterface;

interface InputSourceInterface
{
    /** @return iterable<InputActionInterface> */
    public function poll(): iterable;
}
