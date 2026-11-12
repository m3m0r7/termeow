<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Cli;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class ConsoleApplication extends Application implements ConsoleApplicationInterface
{
    public function __construct(
        private readonly VersionInfoInterface $versionInfo,
    ) {
        parent::__construct('Termeow');
    }

    protected function getDefaultInputDefinition(): InputDefinition
    {
        $definition = parent::getDefaultInputDefinition();
        $options = array_values(array_filter(
            $definition->getOptions(),
            static fn (InputOption $option): bool => !in_array($option->getName(), ['verbose', 'version'], true),
        ));
        $options[] = new InputOption('version', 'v', InputOption::VALUE_NONE, 'Show version and credits');
        $definition->setOptions($options);
        return $definition;
    }

    public function getLongVersion(): string
    {
        return $this->versionInfo->text();
    }
}
