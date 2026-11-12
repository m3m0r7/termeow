<?php

declare(strict_types=1);

namespace Termeow\Domain\Terminal;

final readonly class RgbColor
{
    public function __construct(public int $red, public int $green, public int $blue)
    {
        foreach ([$red, $green, $blue] as $channel) {
            if ($channel < 0 || $channel > 255) {
                throw new \InvalidArgumentException('Color channels must be between 0 and 255');
            }
        }
    }

    public static function fromHex(string $hex): self
    {
        if (preg_match('/^#[0-9a-fA-F]{6}$/D', $hex) !== 1) {
            throw new \InvalidArgumentException('Color must be a #RRGGBB value');
        }

        return new self(
            intval(substr($hex, 1, 2), 16),
            intval(substr($hex, 3, 2), 16),
            intval(substr($hex, 5, 2), 16),
        );
    }

    public function equals(self $other): bool
    {
        return $this->red === $other->red
            && $this->green === $other->green
            && $this->blue === $other->blue;
    }
}
