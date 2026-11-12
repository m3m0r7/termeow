<?php

declare(strict_types=1);

namespace Termeow\Application\Port;

interface InputSourceFactoryInterface
{
    public function create(): InputSourceInterface;
}
