<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Emulation;

use Pnlx\Libvterm\Types\VTermScreen;

readonly class LibvtermScreenHandle implements LibvtermScreenHandleInterface
{
    private function __construct(private VTermScreen $screen)
    {
    }

    public static function of(VTermScreen $screen): self
    {
        return new self($screen);
    }

    public function native(): VTermScreen
    {
        return $this->screen;
    }
}
