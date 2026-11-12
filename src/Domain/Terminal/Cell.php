<?php

declare(strict_types=1);

namespace Termeow\Domain\Terminal;

readonly class Cell implements CellInterface
{
    private function __construct(
        public int $row,
        public int $column,
        public int $width,
        public string $text,
        public ?RgbColorInterface $foreground,
        public ?RgbColorInterface $background,
        public bool $reverse,
        public bool $bold,
        public bool $italic,
    ) {
    }

    public static function of(
        int $row,
        int $column,
        int $width,
        string $text,
        ?RgbColorInterface $foreground,
        ?RgbColorInterface $background,
        bool $reverse,
        bool $bold,
        bool $italic,
    ): self {
        return new self($row, $column, $width, $text, $foreground, $background, $reverse, $bold, $italic);
    }

    public function row(): int
    {
        return $this->row;
    }
    public function column(): int
    {
        return $this->column;
    }
    public function width(): int
    {
        return $this->width;
    }
    public function text(): string
    {
        return $this->text;
    }
    public function foreground(): ?RgbColorInterface
    {
        return $this->foreground;
    }
    public function background(): ?RgbColorInterface
    {
        return $this->background;
    }
    public function reverse(): bool
    {
        return $this->reverse;
    }
    public function bold(): bool
    {
        return $this->bold;
    }
    public function italic(): bool
    {
        return $this->italic;
    }
}
