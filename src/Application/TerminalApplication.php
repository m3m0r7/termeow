<?php

declare(strict_types=1);

namespace Termeow\Application;

use Termeow\Application\Port\InputSourceInterface;
use Termeow\Application\Port\RendererInterface;
use Termeow\Application\Port\ScreenInterface;
use Termeow\Application\Port\SessionInterface;
use Termeow\Application\Port\TerminalApplicationInterface;

readonly class TerminalApplication implements TerminalApplicationInterface
{
    private const FRAME_DELAY_MICROSECONDS = 16000;
    public function __construct(
        private ScreenInterface $screen,
        private SessionInterface $session,
        private RendererInterface $renderer,
        private InputSourceInterface $input,
    ) {
    }

    public function run(): void
    {
        $dirty = true;
        try {
            while ($this->session->running()) {
                foreach ($this->input->poll() as $action) {
                    if ($action->quitRequested()) {
                        return;
                    }
                    if ($action->compositionValue() !== null) {
                        $this->renderer->setComposition($action->compositionValue());
                        $dirty = true;
                    }
                    if ($action->bytes() !== '') {
                        $this->session->write($action->bytes());
                    }
                }
                $output = $this->session->read();
                if ($output !== '') {
                    $this->screen->write($output);
                    $response = $this->screen->readResponses();
                    if ($response !== '') {
                        $this->session->write($response);
                    }
                    $dirty = true;
                }
                if ($dirty) {
                    $this->renderer->draw();
                    $dirty = false;
                }
                usleep(self::FRAME_DELAY_MICROSECONDS);
            }
        } finally {
            $this->session->close();
        }
    }
}
