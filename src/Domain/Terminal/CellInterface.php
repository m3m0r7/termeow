<?php

declare(strict_types=1);

namespace Termeow\Domain\Terminal;

interface CellInterface
{
    public function row(): int;
    public function column(): int;
    public function width(): int;
    public function text(): string;
    public function foreground(): ?RgbColorInterface;
    public function background(): ?RgbColorInterface;
    public function reverse(): bool;
    public function bold(): bool;
    public function italic(): bool;
}
