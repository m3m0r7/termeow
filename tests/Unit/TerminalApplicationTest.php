<?php

declare(strict_types=1);

namespace Termeow\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Termeow\Application\InputAction;
use Termeow\Application\Port\InputSourceInterface;
use Termeow\Application\Port\RendererInterface;
use Termeow\Application\Port\ScreenInterface;
use Termeow\Application\Port\SessionInterface;
use Termeow\Application\TerminalApplication;
use Termeow\Application\TextComposition;
use Termeow\Application\TextCompositionInterface;
use Termeow\Domain\Terminal\Cursor;

final class TerminalApplicationTest extends TestCase
{
    public function testTransfersInputAndOutputAndClosesSession(): void
    {
        $screen = new class implements ScreenInterface {
            public string $received = '';
            public function rows(): int
            {
                return 1;
            }
            public function columns(): int
            {
                return 1;
            }
            public function write(string $data): void
            {
                $this->received .= $data;
            }
            public function cells(): iterable
            {
                return [];
            }
            public function cursor(): Cursor
            {
                return Cursor::at(0, 0);
            }
        };
        $session = new class implements SessionInterface {
            public string $written = '';
            public bool $closed = false;
            private int $polls = 0;
            public function write(string $input): void
            {
                $this->written .= $input;
            }
            public function read(): string
            {
                return 'output';
            }
            public function running(): bool
            {
                return $this->polls++ === 0;
            }
            public function close(): void
            {
                $this->closed = true;
            }
        };
        $renderer = new class implements RendererInterface {
            public int $draws = 0;
            public ?TextCompositionInterface $composition = null;
            public function setComposition(TextCompositionInterface $composition): void
            {
                $this->composition = $composition;
            }
            public function draw(): void
            {
                $this->draws++;
            }
        };
        $input = new class implements InputSourceInterface {
            public function poll(): iterable
            {
                return [InputAction::composition(TextComposition::of("日本", 2, 0)), InputAction::write("\x03")];
            }
        };

        (new TerminalApplication($screen, $session, $renderer, $input))->run();

        self::assertSame("\x03", $session->written);
        self::assertSame('output', $screen->received);
        self::assertSame(1, $renderer->draws);
        self::assertSame("日本", $renderer->composition?->text());
        self::assertTrue($session->closed);
    }
}
