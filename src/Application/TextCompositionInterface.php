<?php

declare(strict_types=1);

namespace Termeow\Application;

interface TextCompositionInterface
{
    public function text(): string;

    public function cursor(): int;

    public function selectionLength(): int;
}
