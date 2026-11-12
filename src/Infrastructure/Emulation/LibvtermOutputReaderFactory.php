<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Emulation;

use Pnlx\Libvterm\Types\VTerm;

readonly class LibvtermOutputReaderFactory implements LibvtermOutputReaderFactoryInterface
{
    public function create(VTerm $terminal): LibvtermOutputReaderInterface
    {
        return LibvtermOutputReader::forTerminal($terminal);
    }
}
