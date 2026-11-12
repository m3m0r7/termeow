<?php

declare(strict_types=1);

namespace Termeow\Domain\Value;

use Termeow\Domain\Exception\InvalidWorkingDirectoryException;

readonly class WorkingDirectory implements WorkingDirectoryInterface
{
    private function __construct(public string $path)
    {
        if (!is_dir($path)) {
            throw new InvalidWorkingDirectoryException("Working directory does not exist: {$path}");
        }
    }

    public static function at(string $path): self
    {
        return new self($path);
    }

    public function path(): string
    {
        return $this->path;
    }
}
