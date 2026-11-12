<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Rendering;

use Termeow\Application\Port\RendererInterface;
use Termeow\Application\TextComposition;
use Termeow\Application\TextCompositionInterface;

class SdlRenderer implements RendererInterface
{
    private TextCompositionInterface $composition;

    public function __construct(
        private SdlDisplayInterface $display,
        private TerminalGridPainterInterface $grid,
        private CompositionPainterInterface $compositionPainter,
        private BitmapExporterInterface $bitmapExporter,
    ) {
        $this->composition = TextComposition::empty();
        $this->positionTextInput();
    }

    public function setComposition(TextCompositionInterface $composition): void
    {
        $this->composition = $composition;
    }

    public function draw(?string $bitmapPath = null): void
    {
        $this->grid->draw($this->composition->text() === '');
        $this->compositionPainter->draw($this->composition);
        $this->positionTextInput();
        if ($bitmapPath !== null) {
            $this->bitmapExporter->saveBitmap($bitmapPath);
        }
        $this->display->present();
    }

    private function positionTextInput(): void
    {
        [$row, $column] = $this->compositionPainter->cursor($this->composition);
        $this->display->positionTextInput($row, $column);
    }
}
