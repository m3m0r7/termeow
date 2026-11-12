<?php

declare(strict_types=1);

namespace Termeow\Application;

final readonly class TextComposition
{
    public function __construct(
        public string $text,
        public int $cursor,
        public int $selectionLength,
    ) {
        if (!mb_check_encoding($text, 'UTF-8')) {
            throw new \InvalidArgumentException('Composition must be UTF-8');
        }
        $length = mb_strlen($text, 'UTF-8');
        if ($cursor < 0 || $cursor > $length || $selectionLength < 0 || $cursor + $selectionLength > $length) {
            throw new \InvalidArgumentException('Composition selection is out of bounds');
        }
    }

    public static function empty(): self
    {
        return new self('', 0, 0);
    }
}
