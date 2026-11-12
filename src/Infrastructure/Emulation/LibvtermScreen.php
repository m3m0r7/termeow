<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Emulation;

use Pnlx\Libvterm\Libvterm as VTerm;
use Pnlx\Libvterm\Types\VTerm as NativeTerminal;
use Pnlx\Libvterm\Types\VTermPos;
use Pnlx\Libvterm\Types\VTermScreen;
use Pnlx\Libvterm\Types\VTermState;
use Termeow\Application\Port\ScreenInterface;
use Termeow\Domain\Terminal\Cursor;
use Termeow\Domain\Terminal\ColorSchemeInterface;
use Termeow\Domain\Value\TerminalDimensionsInterface;
use Termeow\Infrastructure\Emulation\Exception\TerminalEmulationException;

class LibvtermScreen implements ScreenInterface
{
    private const UTF8_ENABLED = 1;
    private const ALT_SCREEN_ENABLED = 1;
    private const FULL_RESET = 1;
    private readonly NativeTerminal $terminal;
    private readonly VTermScreen $screen;
    private readonly VTermState $state;
    private readonly VTermPos $position;
    private readonly LibvtermCellReaderInterface $reader;
    private readonly LibvtermOutputReaderInterface $output;

    public function __construct(
        private readonly TerminalDimensionsInterface $dimensions,
        ColorSchemeInterface $colors,
        LibvtermPaletteInstallerInterface $palette,
        LibvtermCellReaderFactoryInterface $readers,
        LibvtermOutputReaderFactoryInterface $outputs,
    ) {
        $terminal = VTerm::vterm_new($dimensions->rows(), $dimensions->columns())
            ?? throw new TerminalEmulationException('vterm_new failed');
        try {
            VTerm::vterm_set_utf8($terminal, self::UTF8_ENABLED);
            $screen = VTerm::vterm_obtain_screen($terminal)
                ?? throw new TerminalEmulationException('vterm_obtain_screen failed');
            $state = VTerm::vterm_obtain_state($terminal)
                ?? throw new TerminalEmulationException('vterm_obtain_state failed');
            VTerm::vterm_screen_enable_altscreen($screen, self::ALT_SCREEN_ENABLED);
            VTerm::vterm_screen_reset($screen, self::FULL_RESET);
            $palette->install($state, $colors);
            $this->terminal = $terminal;
            $this->screen = $screen;
            $this->state = $state;
            $this->position = new VTermPos();
            $this->reader = $readers->create($screen, $dimensions);
            $this->output = $outputs->create($terminal);
        } catch (\Throwable $exception) {
            VTerm::vterm_free($terminal);
            throw $exception;
        }
    }

    public function rows(): int
    {
        return $this->dimensions->rows();
    }
    public function columns(): int
    {
        return $this->dimensions->columns();
    }

    public function write(string $data): void
    {
        VTerm::vterm_input_write($this->terminal, $data, strlen($data));
        VTerm::vterm_screen_flush_damage($this->screen);
    }

    public function readResponses(): string
    {
        return $this->output->read();
    }

    public function cursor(): Cursor
    {
        VTerm::vterm_state_get_cursorpos($this->state, $this->position);
        return Cursor::at($this->position->getRow(), $this->position->getCol());
    }

    public function cells(): iterable
    {
        return $this->reader->cells();
    }
    public function rowText(int $row): string
    {
        return $this->reader->rowText($row);
    }

    /** @return iterable<array{row:int, text:string}> */
    public function lines(): iterable
    {
        for ($row = 0; $row < $this->rows(); $row++) {
            $text = $this->rowText($row);
            if ($text !== '') {
                yield ['row' => $row, 'text' => $text];
            }
        }
    }

    public function __destruct()
    {
        VTerm::vterm_free($this->terminal);
    }
}
