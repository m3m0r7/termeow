<?php

declare(strict_types=1);

namespace Termeow\Application;

readonly class InputAction implements InputActionInterface
{
    private function __construct(
        public string $bytes,
        public bool $quit,
        public ?TextCompositionInterface $composition,
    ) {
    }

    public function bytes(): string
    {
        return $this->bytes;
    }
    public function quitRequested(): bool
    {
        return $this->quit;
    }
    public function compositionValue(): ?TextCompositionInterface
    {
        return $this->composition;
    }

    public static function write(string $bytes): self
    {
        return new self($bytes, false, null);
    }

    public static function quit(): self
    {
        return new self('', true, null);
    }

    public static function composition(TextCompositionInterface $composition): self
    {
        return new self('', false, $composition);
    }
}
