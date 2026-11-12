<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Emulation;

use Termeow\Domain\Terminal\CellInterface;

interface LibvtermCellReaderInterface
{
    /** @return iterable<CellInterface> */
    public function cells(): iterable;

    public function rowText(int $row): string;
}
