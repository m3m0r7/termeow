<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Process;

use Termeow\Infrastructure\Process\Exception\PtyIoException;
use Termeow\Application\Port\PtyInputWriterInterface;
use Termeow\Infrastructure\Process\Port\PtyPipeProviderInterface;

readonly class PtyInputWriter implements PtyInputWriterInterface
{
    public function __construct(private PtyPipeProviderInterface $pipes)
    {
    }

    public function write(string $input): void
    {
        $remaining = $input;
        while ($remaining !== '') {
            $written = fwrite($this->pipes->inputPipe(), $remaining);
            if ($written === false || $written === 0) {
                throw new PtyIoException('Could not write to the pseudo terminal');
            }
            $remaining = substr($remaining, $written);
        }
    }
}
