<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Process;

use Termeow\Application\Port\PtyOutputReaderInterface;
use Termeow\Infrastructure\Process\Port\PtyPipeProviderInterface;

readonly class PtyOutputReader implements PtyOutputReaderInterface
{
    private const READ_BUFFER_BYTES = 65536;

    public function __construct(private PtyPipeProviderInterface $pipes)
    {
    }

    public function read(): string
    {
        $output = '';
        foreach ($this->pipes->outputPipes() as $pipe) {
            while (($chunk = fread($pipe, self::READ_BUFFER_BYTES)) !== false && $chunk !== '') {
                $output .= $chunk;
            }
        }
        return $output;
    }
}
