<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Emulation;

use Pnlx\Libvterm\Libvterm as VTerm;
use Pnlx\Libvterm\Types\VTermColor;
use Pnlx\Libvterm\Types\VTermPos;
use Pnlx\Libvterm\Types\VTermScreenCell;
use Termeow\Domain\Exception\InvalidScreenRowException;
use Termeow\Domain\Terminal\Cell;
use Termeow\Domain\Terminal\RgbColor;
use Termeow\Domain\Value\TerminalDimensionsInterface;

readonly class LibvtermCellReader implements LibvtermCellReaderInterface
{
    private const MAX_CODEPOINTS_PER_CELL = 6;
    private VTermPos $position;
    private VTermScreenCell $cell;

    public function __construct(
        private LibvtermScreenHandleInterface $screen,
        private TerminalDimensionsInterface $dimensions,
    ) {
        $this->position = new VTermPos();
        $this->cell = new VTermScreenCell();
    }

    public function cells(): iterable
    {
        for ($row = 0; $row < $this->dimensions->rows(); $row++) {
            for ($col = 0; $col < $this->dimensions->columns(); $col++) {
                $this->position->setRow($row)->setCol($col);
                VTerm::vterm_screen_get_cell($this->screen->native(), $this->position, $this->cell);
                $width = ord($this->cell->cdata()[0]->width);
                if ($width === 0) {
                    continue;
                }
                $text = $this->character();
                $foreground = $this->color($this->cell->getFg(), VTermColorFlag::DEFAULT_FOREGROUND->value);
                $background = $this->color($this->cell->getBg(), VTermColorFlag::DEFAULT_BACKGROUND->value);
                $attrs = $this->cell->getAttrs();
                $reverse = $attrs->getReverse() !== 0;
                if ($text !== ' ' || $background !== null || $reverse) {
                    yield Cell::of(
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
        if ($row < 0 || $row >= $this->dimensions->rows()) {
            throw new InvalidScreenRowException('Invalid screen row');
        }
        $text = '';
        for ($col = 0; $col < $this->dimensions->columns(); $col++) {
            $this->position->setRow($row)->setCol($col);
            VTerm::vterm_screen_get_cell($this->screen->native(), $this->position, $this->cell);
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
        for ($index = 0; $index < self::MAX_CODEPOINTS_PER_CELL && $points[$index] !== 0; $index++) {
            $text .= mb_chr($points[$index], 'UTF-8');
        }
        return $text === '' ? ' ' : $text;
    }

    private function color(VTermColor $color, int $defaultFlag): ?RgbColor
    {
        if (($color->getType() & $defaultFlag) !== 0) {
            return null;
        }
        VTerm::vterm_screen_convert_color_to_rgb($this->screen->native(), $color);
        $rgb = $color->cdata()[0]->rgb;
        return RgbColor::of($rgb->red, $rgb->green, $rgb->blue);
    }
}
