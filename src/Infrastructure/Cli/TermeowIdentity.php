<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Cli;

use Composer\InstalledVersions;
use Termeow\Application\Port\TerminalIdentityInterface;

readonly class TermeowIdentity implements TerminalIdentityInterface
{
    private const NAME = 'termeow';
    private const PACKAGE_NAME = 'm3m0r7/termeow';
    private const FALLBACK_VERSION = '0.1.0';
    private const TERMINAL_TYPE = 'xterm-256color';

    public function name(): string
    {
        return self::NAME;
    }
    public function packageName(): string
    {
        return self::PACKAGE_NAME;
    }
    public function version(): string
    {
        return InstalledVersions::getPrettyVersion(self::PACKAGE_NAME) ?? self::FALLBACK_VERSION;
    }
    public function terminalType(): string
    {
        return self::TERMINAL_TYPE;
    }
}
