<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Emulation\Exception;

use Termeow\Domain\Exception\TermeowExceptionInterface;

class TerminalEmulationException extends \RuntimeException implements TermeowExceptionInterface
{
}
