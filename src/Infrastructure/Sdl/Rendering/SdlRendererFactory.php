<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Rendering;

use Termeow\Application\Port\RendererFactoryInterface;
use Termeow\Application\Port\TerminalViewInterface;
use Termeow\Domain\Configuration\TerminalSettingsInterface;

readonly class SdlRendererFactory implements RendererFactoryInterface
{
    public function __construct(private SdlDisplayFactoryInterface $displays)
    {
    }

    public function create(TerminalViewInterface $screen, TerminalSettingsInterface $settings): SdlRenderer
    {
        $display = $this->displays->create($screen, $settings);
        $canvas = new SdlCanvas($display, new SdlGlyphPainter($display));
        $composition = new SdlCompositionPainter($screen, $canvas, $settings->colors(), $display);
        return new SdlRenderer(
            $display,
            new TerminalGridPainter($screen, $settings->colors(), $display, $canvas),
            $composition,
            new SdlBitmapExporter($display),
        );
    }
}
