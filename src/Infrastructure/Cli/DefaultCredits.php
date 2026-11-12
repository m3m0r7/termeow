<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Cli;

readonly class DefaultCredits implements CreditsInterface
{
    public function lines(): array
    {
        return ['Author: m3m0r7', 'Built with: pnl, SDL2, SDL_ttf, libvterm'];
    }
}
