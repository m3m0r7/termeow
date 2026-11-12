<?php

declare(strict_types=1);

namespace Termeow\Application;

use Termeow\Application\Port\TerminalFactoryInterface;
use Termeow\Application\Port\TerminalRunnerInterface;
use Termeow\Domain\Value\WorkingDirectoryInterface;
use Termeow\Application\Port\ConfigurationLoaderInterface;
use Termeow\Application\Port\TerminalOptionsFactoryInterface;

readonly class TerminalRunner implements TerminalRunnerInterface
{
    public function __construct(
        private ConfigurationLoaderInterface $configuration,
        private TerminalOptionsFactoryInterface $options,
        private TerminalFactoryInterface $factory,
        private WorkingDirectoryInterface $directory,
    ) {
    }

    public function runTerminal(): void
    {
        $options = $this->options->create($this->configuration->load());
        $this->factory->create($options, $this->directory)->run();
    }
}
