<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Process;

use Termeow\Application\Port\PtyInputWriterInterface;
use Termeow\Application\Port\PtyOutputReaderInterface;
use Termeow\Application\Port\SessionInterface;
use Termeow\Application\Port\SessionLifecycleInterface;

readonly class ScriptPtySession implements SessionInterface
{
    public function __construct(
        private PtyInputWriterInterface $writer,
        private PtyOutputReaderInterface $reader,
        private SessionLifecycleInterface $lifecycle,
    ) {
    }

    public function write(string $input): void
    {
        $this->writer->write($input);
    }

    public function read(): string
    {
        return $this->reader->read();
    }

    public function running(): bool
    {
        return $this->lifecycle->running();
    }

    public function close(): void
    {
        $this->lifecycle->close();
    }
}
