<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Emulation;

use Pnlx\Libvterm\Types\VTermScreen;
use Termeow\Domain\Value\TerminalDimensionsInterface;

readonly class LibvtermCellReaderFactory implements LibvtermCellReaderFactoryInterface
{
    public function create(VTermScreen $screen, TerminalDimensionsInterface $dimensions): LibvtermCellReaderInterface
    {
        return new LibvtermCellReader(LibvtermScreenHandle::of($screen), $dimensions);
    }
}
