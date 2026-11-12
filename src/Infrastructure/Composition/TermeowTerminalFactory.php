<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Composition;

use Termeow\Application\Port\InputSourceFactoryInterface;
use Termeow\Application\Port\RendererFactoryInterface;
use Termeow\Application\Port\ScreenFactoryInterface;
use Termeow\Application\Port\SessionFactoryInterface;
use Termeow\Application\Port\TerminalApplicationInterface;
use Termeow\Application\Port\TerminalFactoryInterface;
use Termeow\Application\TerminalApplication;
use Termeow\Domain\Configuration\TerminalSettingsInterface;
use Termeow\Domain\Value\TerminalDimensionsInterface;
use Termeow\Domain\Value\WorkingDirectoryInterface;
use Termeow\Application\Port\TerminalRuntimeInitializerInterface;

readonly class TermeowTerminalFactory implements TerminalFactoryInterface
{
    public function __construct(
        private TerminalRuntimeInitializerInterface $runtime,
        private TerminalDimensionsInterface $dimensions,
        private ScreenFactoryInterface $screens,
        private RendererFactoryInterface $renderers,
        private SessionFactoryInterface $sessions,
        private InputSourceFactoryInterface $inputs,
    ) {
    }

    public function create(
        TerminalSettingsInterface $settings,
        WorkingDirectoryInterface $directory,
    ): TerminalApplicationInterface {
        $this->runtime->initialize();
        $screen = $this->screens->create($this->dimensions, $settings->colors());
        $renderer = $this->renderers->create($screen, $settings);
        $session = $this->sessions->create($this->dimensions, $directory);
        $input = $this->inputs->create();
        return new TerminalApplication($screen, $session, $renderer, $input);
    }
}
