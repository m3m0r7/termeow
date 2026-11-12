<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Pnl;

use Termeow\Domain\Exception\TermeowExceptionInterface;

class PnlBindingException extends \RuntimeException implements TermeowExceptionInterface
{
}
