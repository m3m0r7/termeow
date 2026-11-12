<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Emulation;

use Pnlx\Libvterm\Libvterm as VTerm;
use Pnlx\Libvterm\Types\VTerm as NativeTerminal;
use Termeow\Infrastructure\Emulation\Exception\TerminalEmulationException;

readonly class LibvtermOutputReader implements LibvtermOutputReaderInterface
{
    private const BUFFER_BYTES = 4096;

    private function __construct(private NativeTerminal $terminal)
    {
    }

    public static function forTerminal(NativeTerminal $terminal): self
    {
        return new self($terminal);
    }

    public function read(): string
    {
        $responses = '';
        do {
            $buffer = str_repeat("\0", self::BUFFER_BYTES);
            $length = VTerm::vterm_output_read($this->terminal, $buffer, self::BUFFER_BYTES)
                ?? throw new TerminalEmulationException('vterm_output_read failed');
            if (!is_string($buffer)) {
                throw new TerminalEmulationException('vterm_output_read returned an invalid buffer');
            }
            $bytes = $length->toInt();
            $responses .= substr($buffer, 0, $bytes);
        } while ($bytes === self::BUFFER_BYTES);

        return $responses;
    }
}
