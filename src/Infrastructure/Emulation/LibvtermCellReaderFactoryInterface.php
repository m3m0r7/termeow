<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Emulation;

use Pnlx\Libvterm\Types\VTermScreen;
use Termeow\Domain\Value\TerminalDimensionsInterface;

interface LibvtermCellReaderFactoryInterface
{
    public function create(VTermScreen $screen, TerminalDimensionsInterface $dimensions): LibvtermCellReaderInterface;
}
