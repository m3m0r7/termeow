<?php

declare(strict_types=1);

namespace Termeow\Domain\Exception;

class InvalidTerminalValueException extends \InvalidArgumentException implements TermeowExceptionInterface
{
}
