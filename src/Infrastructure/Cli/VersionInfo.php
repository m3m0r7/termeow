<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Cli;

use Termeow\Application\Port\TerminalIdentityInterface;

readonly class VersionInfo implements VersionInfoInterface
{
    public function __construct(
        private TerminalIdentityInterface $identity,
        private CreditsInterface $credits,
    ) {
    }

    public function text(): string
    {
        return implode("\n", [
            ucfirst($this->identity->name()) . ' ' . $this->identity->version(),
            'PHP ' . PHP_VERSION,
            ...$this->credits->lines(),
        ]);
    }
}
