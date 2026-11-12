<?php

declare(strict_types=1);

namespace Termeow\Domain\Terminal;

interface ColorSchemeInterface
{
    public function foreground(): RgbColorInterface;

    public function background(): RgbColorInterface;

    public function ansi(int $index): RgbColorInterface;

    /** @return list<RgbColorInterface> */
    public function palette(): array;
}
