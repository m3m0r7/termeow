<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Rendering;

use Termeow\Application\Port\TerminalViewInterface;
use Termeow\Domain\Terminal\ColorSchemeInterface;

readonly class TerminalGridPainter implements TerminalGridPainterInterface
{
    private const CURSOR_BASELINE_OFFSET = 3;
    private const CURSOR_THICKNESS = 2;

    public function __construct(
        private TerminalViewInterface $screen,
        private ColorSchemeInterface $colors,
        private SdlDisplayInterface $display,
        private SdlCanvasInterface $canvas,
    ) {
    }

    public function draw(bool $showCursor): void
    {
        $this->display->clear($this->colors->background());
        foreach ($this->screen->cells() as $glyph) {
            $foreground = $glyph->foreground() ?? $this->colors->foreground();
            $background = $glyph->background() ?? $this->colors->background();
            if ($glyph->reverse()) {
                [$foreground, $background] = [$background, $foreground];
            }
            if (!$background->equals($this->colors->background())) {
                $this->canvas->fill(
                    $this->display->padding() + $glyph->column() * $this->display->cellWidth(),
                    $this->display->padding() + $glyph->row() * $this->display->lineHeight(),
                    $glyph->width() * $this->display->cellWidth(),
                    $this->display->lineHeight(),
                    $background,
                );
            }
            if ($glyph->text() === ' ') {
                continue;
            }
            $this->canvas->drawText(
                $glyph->text(),
                $this->display->padding() + $glyph->column() * $this->display->cellWidth(),
                $this->display->padding() + $glyph->row() * $this->display->lineHeight(),
                $glyph->width() * $this->display->cellWidth(),
                $foreground,
                $glyph->bold(),
                $glyph->italic(),
            );
        }
        if (!$showCursor) {
            return;
        }
        $cursor = $this->screen->cursor();
        $row = $cursor->row();
        $column = $cursor->column();
        if ($row < 0 || $row >= $this->screen->rows() || $column < 0 || $column >= $this->screen->columns()) {
            return;
        }
        $this->canvas->fill(
            $this->display->padding() + $column * $this->display->cellWidth(),
            $this->display->padding() + ($row + 1) * $this->display->lineHeight() - self::CURSOR_BASELINE_OFFSET,
            $this->display->cellWidth(),
            self::CURSOR_THICKNESS,
            $this->colors->foreground(),
        );
    }
}
