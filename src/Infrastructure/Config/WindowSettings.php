<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Config;

readonly class WindowSettings implements WindowSettingsInterface
{
    private function __construct(private string $title)
    {
    }

    public static function titled(string $title): self
    {
        return new self($title);
    }

    public function title(): string
    {
        return $this->title;
    }
}
