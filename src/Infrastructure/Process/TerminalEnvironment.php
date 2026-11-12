<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Process;

use Termeow\Domain\Value\TerminalDimensionsInterface;
use Termeow\Domain\Value\WorkingDirectoryInterface;
use Termeow\Application\Port\TerminalIdentityInterface;
use Termeow\Infrastructure\Process\Exception\PtyStartException;
use Termeow\Infrastructure\Process\Port\PtyEnvironmentInterface;

readonly class TerminalEnvironment implements PtyEnvironmentInterface
{
    public function __construct(
        private TerminalIdentityInterface $identity,
        private WorkingDirectoryInterface $package,
        private PtyScriptSettingsInterface $script,
    ) {
    }

    public function variables(TerminalDimensionsInterface $dimensions): array
    {
        $shell = getenv('SHELL') ?: $this->script->shellPath();
        if (!is_executable($shell)) {
            throw new PtyStartException("Shell is not executable: {$shell}");
        }
        $environment = getenv();
        $environment['SHELL'] = $shell;
        $environment['TERM'] = $this->identity->terminalType();
        $environment['TERM_PROGRAM'] = $this->identity->name();
        $environment['TERM_PROGRAM_VERSION'] = $this->identity->version();
        $path = $environment['PATH'] ?? '';
        $bin = $this->package->path() . '/bin';
        $environment['PATH'] = in_array($bin, explode(PATH_SEPARATOR, $path), true)
            ? $path
            : $bin . ($path === '' ? '' : PATH_SEPARATOR . $path);
        $environment['COLUMNS'] = (string) $dimensions->columns();
        $environment['LINES'] = (string) $dimensions->rows();
        return $environment;
    }
}
