<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Exception;

use Termeow\Domain\Exception\TermeowExceptionInterface;

class InputDecodingException extends \RuntimeException implements TermeowExceptionInterface
{
}
