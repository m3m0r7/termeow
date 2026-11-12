<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Rendering;

use Termeow\Application\Port\TerminalViewInterface;
use Termeow\Application\TextCompositionInterface;
use Termeow\Domain\Terminal\ColorSchemeInterface;
use Termeow\Domain\Terminal\AnsiColorIndex;

readonly class SdlCompositionPainter implements CompositionPainterInterface
{
    private const UNDERLINE_BASELINE_OFFSET = 2;
    private const CARET_HEIGHT_OFFSET = 2;
    public function __construct(
        private TerminalViewInterface $screen,
        private SdlCanvasInterface $canvas,
        private ColorSchemeInterface $colors,
        private TerminalMetricsInterface $metrics,
    ) {
    }

    public function draw(TextCompositionInterface $composition): void
    {
        if ($composition->text() === '') {
            return;
        }
        $cursor = $this->screen->cursor();
        $row = $cursor->row();
        $column = $cursor->column();
        foreach (mb_str_split($composition->text()) as $index => $character) {
            $width = $this->metrics->characterCells($character);
            if ($column + $width > $this->screen->columns()) {
                $row++;
                $column = 0;
            }
            if ($row >= $this->screen->rows()) {
                break;
            }
            $x = $this->metrics->padding() + $column * $this->metrics->cellWidth();
            $y = $this->metrics->padding() + $row * $this->metrics->lineHeight();
            $selected = $index >= $composition->cursor()
                && $index < $composition->cursor() + $composition->selectionLength();
            $this->canvas->fill(
                $x,
                $y,
                $width * $this->metrics->cellWidth(),
                $this->metrics->lineHeight(),
                $selected ? $this->colors->ansi(AnsiColorIndex::BLUE->value) : $this->colors->background(),
            );
            $this->canvas->drawText(
                $character,
                $x,
                $y,
                $width * $this->metrics->cellWidth(),
                $this->colors->foreground(),
                false,
                false,
            );
            $this->canvas->fill(
                $x,
                $y + $this->metrics->lineHeight() - self::UNDERLINE_BASELINE_OFFSET,
                $width * $this->metrics->cellWidth(),
                1,
                $this->colors->ansi(AnsiColorIndex::CYAN->value),
            );
            $column += $width;
        }
        [$caretRow, $caretColumn] = $this->cursor($composition);
        $this->canvas->fill(
            $this->metrics->padding() + $caretColumn * $this->metrics->cellWidth(),
            $this->metrics->padding() + $caretRow * $this->metrics->lineHeight(),
            1,
            $this->metrics->lineHeight() - self::CARET_HEIGHT_OFFSET,
            $this->colors->foreground(),
        );
    }

    /** @return array{int, int} */
    public function cursor(TextCompositionInterface $composition): array
    {
        $cursor = $this->screen->cursor();
        $row = $cursor->row();
        $column = $cursor->column();
        foreach (mb_str_split(mb_substr($composition->text(), 0, $composition->cursor())) as $character) {
            $width = $this->metrics->characterCells($character);
            if ($column + $width > $this->screen->columns()) {
                $row++;
                $column = 0;
            }
            $column += $width;
        }
        if ($column >= $this->screen->columns()) {
            $row++;
            $column = 0;
        }
        return [min($row, $this->screen->rows() - 1), $column];
    }
}
