<?php

declare(strict_types=1);

namespace Termeow\Domain\Terminal;

use Termeow\Domain\Exception\InvalidTerminalValueException;

readonly class RgbColor implements RgbColorInterface
{
    private const MAX_CHANNEL = 255;
    private function __construct(public int $red, public int $green, public int $blue)
    {
        foreach ([$red, $green, $blue] as $channel) {
            if ($channel < 0 || $channel > self::MAX_CHANNEL) {
                throw new InvalidTerminalValueException('Color channels must be between 0 and 255');
            }
        }
    }

    public static function of(int $red, int $green, int $blue): self
    {
        return new self($red, $green, $blue);
    }

    public function red(): int
    {
        return $this->red;
    }
    public function green(): int
    {
        return $this->green;
    }
    public function blue(): int
    {
        return $this->blue;
    }

    public static function fromHex(string $hex): self
    {
        if (preg_match('/^#[0-9a-fA-F]{6}$/D', $hex) !== 1) {
            throw new InvalidTerminalValueException('Color must be a #RRGGBB value');
        }

        return new self(
            intval(substr($hex, 1, 2), 16),
            intval(substr($hex, 3, 2), 16),
            intval(substr($hex, 5, 2), 16),
        );
    }

    public function equals(RgbColorInterface $other): bool
    {
        return $this->red === $other->red()
            && $this->green === $other->green()
            && $this->blue === $other->blue();
    }
}
