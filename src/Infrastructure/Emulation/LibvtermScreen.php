<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Emulation;

use Pnlx\Libvterm\Libvterm as VTerm;
use Termeow\Application\Port\Screen;
use Termeow\Domain\Terminal\Cell;
use Termeow\Domain\Terminal\ColorScheme;
use Termeow\Domain\Terminal\Cursor;
use Termeow\Domain\Terminal\RgbColor;
use Termeow\Infrastructure\Pnl\NativeScalar;
use Pnlx\Libvterm\Types\VTermColor;
use Pnlx\Libvterm\Types\VTermPos;
use Pnlx\Libvterm\Types\VTermScreenCell;

use const Pnlx\Libvterm\VTERM_COLOR_DEFAULT_BG;
use const Pnlx\Libvterm\VTERM_COLOR_DEFAULT_FG;
use const Pnlx\Libvterm\VTERM_COLOR_RGB;

final class LibvtermScreen implements Screen
{
    private readonly object $terminal;
    private readonly object $screen;
    private readonly object $state;
    private readonly VTermPos $position;
    private readonly VTermScreenCell $cell;

    public function __construct(public readonly int $rows, public readonly int $columns, ColorScheme $colors)
    {
        $this->terminal = VTerm::vterm_new($rows, $columns)
            ?? throw new \RuntimeException('vterm_new failed');
        VTerm::vterm_set_utf8($this->terminal, 1);
        $this->screen = VTerm::vterm_obtain_screen($this->terminal)
            ?? throw new \RuntimeException('vterm_obtain_screen failed');
        $this->state = VTerm::vterm_obtain_state($this->terminal)
            ?? throw new \RuntimeException('vterm_obtain_state failed');
        VTerm::vterm_screen_enable_altscreen($this->screen, 1);
        VTerm::vterm_screen_reset($this->screen, 1);
        $paletteColor = new VTermColor();
        $paletteColor->setType(NativeScalar::int(VTERM_COLOR_RGB));
        foreach ($colors->ansi as $index => $color) {
            $paletteColor->cdata()[0]->rgb->red = $color->red;
            $paletteColor->cdata()[0]->rgb->green = $color->green;
            $paletteColor->cdata()[0]->rgb->blue = $color->blue;
            VTerm::vterm_state_set_palette_color($this->state, $index, $paletteColor);
        }
        $this->position = new VTermPos();
        $this->cell = new VTermScreenCell();
    }

    public function rows(): int
    {
        return $this->rows;
    }

    public function columns(): int
    {
        return $this->columns;
    }

    public function __destruct()
    {
        VTerm::vterm_free($this->terminal);
    }

    public function write(string $data): void
    {
        VTerm::vterm_input_write($this->terminal, $data, strlen($data));
        VTerm::vterm_screen_flush_damage($this->screen);
    }

    public function cursor(): Cursor
    {
        VTerm::vterm_state_get_cursorpos($this->state, $this->position);
        return new Cursor($this->position->getRow(), $this->position->getCol());
    }

    /** @return iterable<array{row:int, text:string}> */
    public function lines(): iterable
    {
        for ($row = 0; $row < $this->rows; $row++) {
            $text = $this->rowText($row);
            if ($text !== '') {
                yield ['row' => $row, 'text' => $text];
            }
        }
    }

    /** @return iterable<Cell> */
    public function cells(): iterable
    {
        for ($row = 0; $row < $this->rows; $row++) {
            for ($col = 0; $col < $this->columns; $col++) {
                $this->position->setRow($row)->setCol($col);
                VTerm::vterm_screen_get_cell($this->screen, $this->position, $this->cell);
                $width = ord($this->cell->cdata()[0]->width);
                if ($width === 0) {
                    continue;
                }
                $text = $this->character();
                $foreground = $this->color($this->cell->getFg(), NativeScalar::int(VTERM_COLOR_DEFAULT_FG));
                $background = $this->color($this->cell->getBg(), NativeScalar::int(VTERM_COLOR_DEFAULT_BG));
                $attrs = $this->cell->getAttrs();
                $reverse = $attrs->getReverse() !== 0;
                if ($text !== ' ' || $background !== null || $reverse) {
                    yield new Cell(
                        $row,
                        $col,
                        $width,
                        $text,
                        $foreground,
                        $background,
                        $reverse,
                        $attrs->getBold() !== 0,
                        $attrs->getItalic() !== 0,
                    );
                }
            }
        }
    }

    public function rowText(int $row): string
    {
        if ($row < 0 || $row >= $this->rows) {
            throw new \OutOfRangeException('Invalid screen row');
        }
        $text = '';
        for ($col = 0; $col < $this->columns; $col++) {
            $this->position->setRow($row)->setCol($col);
            VTerm::vterm_screen_get_cell($this->screen, $this->position, $this->cell);
            if (ord($this->cell->cdata()[0]->width) !== 0) {
                $text .= $this->character();
            }
        }
        return rtrim($text);
    }

    private function character(): string
    {
        $points = $this->cell->cdata()[0]->chars;
        $text = '';
        for ($index = 0; $index < 6 && $points[$index] !== 0; $index++) {
            $text .= mb_chr($points[$index], 'UTF-8');
        }
        return $text === '' ? ' ' : $text;
    }

    private function color(VTermColor $color, int $defaultFlag): ?RgbColor
    {
        if (($color->getType() & $defaultFlag) !== 0) {
            return null;
        }
        VTerm::vterm_screen_convert_color_to_rgb($this->screen, $color);
        $rgb = $color->cdata()[0]->rgb;
        return new RgbColor($rgb->red, $rgb->green, $rgb->blue);
    }
}
