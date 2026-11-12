<?php

declare(strict_types=1);

namespace Termeow\Application;

use Termeow\Application\Port\InputSource;
use Termeow\Application\Port\Renderer;
use Termeow\Application\Port\Screen;
use Termeow\Application\Port\Session;

final readonly class TerminalApplication
{
    public function __construct(
        private Screen $screen,
        private Session $session,
        private Renderer $renderer,
        private InputSource $input,
    ) {
    }

    public function run(): void
    {
        $dirty = true;
        try {
            while ($this->session->running()) {
                foreach ($this->input->poll() as $action) {
                    if ($action->quit) {
                        return;
                    }
                    if ($action->composition !== null) {
                        $this->renderer->setComposition($action->composition);
                        $dirty = true;
                    }
                    if ($action->bytes !== '') {
                        $this->session->write($action->bytes);
                    }
                }
                $output = $this->session->read();
                if ($output !== '') {
                    $this->screen->write($output);
                    $dirty = true;
                }
                if ($dirty) {
                    $this->renderer->draw();
                    $dirty = false;
                }
                usleep(16000);
            }
        } finally {
            $this->session->close();
        }
    }
}
