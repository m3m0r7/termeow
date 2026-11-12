<?php

declare(strict_types=1);

namespace Termeow\Application;

final readonly class InputAction
{
    private function __construct(
        public string $bytes,
        public bool $quit,
        public ?TextComposition $composition,
    ) {
    }

    public static function write(string $bytes): self
    {
        return new self($bytes, false, null);
    }

    public static function quit(): self
    {
        return new self('', true, null);
    }

    public static function composition(TextComposition $composition): self
    {
        return new self('', false, $composition);
    }
}
