<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Emulation;

use Pnlx\Libvterm\Types\VTermScreen;

interface LibvtermScreenHandleInterface
{
    public function native(): VTermScreen;
}
