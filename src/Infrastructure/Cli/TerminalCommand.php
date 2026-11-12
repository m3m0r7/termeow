<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Cli;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Termeow\Application\Port\TerminalRunnerInterface;

#[AsCommand(name: 'termeow', description: 'Launch the Termeow terminal')]
class TerminalCommand extends Command implements TerminalCommandInterface
{
    public function __construct(
        private readonly TerminalRunnerInterface $runner,
        private readonly VersionInfoInterface $version,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('version') || getenv('TERM_PROGRAM') === 'termeow') {
            $output->writeln($this->version->text());
            return Command::SUCCESS;
        }
        $this->runner->runTerminal();
        return Command::SUCCESS;
    }
}
