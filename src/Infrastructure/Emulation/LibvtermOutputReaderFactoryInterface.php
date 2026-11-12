<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Emulation;

use Pnlx\Libvterm\Types\VTerm;

interface LibvtermOutputReaderFactoryInterface
{
    public function create(VTerm $terminal): LibvtermOutputReaderInterface;
}
