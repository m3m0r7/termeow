<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Input;

use Termeow\Application\Port\InputSourceFactoryInterface;
use Termeow\Application\Port\InputSourceInterface;

readonly class SdlInputSourceFactory implements InputSourceFactoryInterface
{
    public function create(): InputSourceInterface
    {
        return new SdlInputSource(new SdlInputEventHandler(new SdlKeyTranslator(), new SdlTextDecoder()));
    }
}
