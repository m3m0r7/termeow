<?php

declare(strict_types=1);

namespace Termeow\Domain\Exception;

class InvalidScreenRowException extends \OutOfRangeException implements TermeowExceptionInterface
{
}
