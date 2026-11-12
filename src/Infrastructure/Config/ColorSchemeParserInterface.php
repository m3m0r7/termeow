<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Config;

use Termeow\Domain\Terminal\ColorSchemeInterface;

interface ColorSchemeParserInterface
{
    public function fromConfig(mixed $config): ColorSchemeInterface;
}
