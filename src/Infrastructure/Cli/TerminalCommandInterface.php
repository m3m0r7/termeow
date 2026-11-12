<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Cli;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface TerminalCommandInterface
{
    public function run(InputInterface $input, OutputInterface $output): int;
}
