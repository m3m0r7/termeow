<?php

declare(strict_types=1);

namespace Termeow\Application;

use Termeow\Domain\Exception\InvalidCompositionException;

readonly class TextComposition implements TextCompositionInterface
{
    private function __construct(
        public string $text,
        public int $cursor,
        public int $selectionLength,
    ) {
        if (!mb_check_encoding($text, 'UTF-8')) {
            throw new InvalidCompositionException('Composition must be UTF-8');
        }
        $length = mb_strlen($text, 'UTF-8');
        if ($cursor < 0 || $cursor > $length || $selectionLength < 0 || $cursor + $selectionLength > $length) {
            throw new InvalidCompositionException('Composition selection is out of bounds');
        }
    }

    public static function of(string $text, int $cursor, int $selectionLength): self
    {
        return new self($text, $cursor, $selectionLength);
    }

    public function text(): string
    {
        return $this->text;
    }
    public function cursor(): int
    {
        return $this->cursor;
    }
    public function selectionLength(): int
    {
        return $this->selectionLength;
    }

    public static function empty(): self
    {
        return new self('', 0, 0);
    }
}
