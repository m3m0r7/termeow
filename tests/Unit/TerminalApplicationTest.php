<?php

declare(strict_types=1);

namespace Termeow\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Termeow\Application\InputAction;
use Termeow\Application\Port\InputSource;
use Termeow\Application\Port\Renderer;
use Termeow\Application\Port\Screen;
use Termeow\Application\Port\Session;
use Termeow\Application\TerminalApplication;
use Termeow\Application\TextComposition;
use Termeow\Domain\Terminal\Cursor;

final class TerminalApplicationTest extends TestCase
{
    public function testTransfersInputAndOutputAndClosesSession(): void
    {
        $screen = new class implements Screen {
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
                return new Cursor(0, 0);
            }
        };
        $session = new class implements Session {
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
        $renderer = new class implements Renderer {
            public int $draws = 0;
            public ?TextComposition $composition = null;
            public function setComposition(TextComposition $composition): void
            {
                $this->composition = $composition;
            }
            public function draw(): void
            {
                $this->draws++;
            }
        };
        $input = new class implements InputSource {
            public function poll(): iterable
            {
                return [InputAction::composition(new TextComposition("日本", 2, 0)), InputAction::write("\x03")];
            }
        };

        (new TerminalApplication($screen, $session, $renderer, $input))->run();

        self::assertSame("\x03", $session->written);
        self::assertSame('output', $screen->received);
        self::assertSame(1, $renderer->draws);
        self::assertSame("日本", $renderer->composition?->text);
        self::assertTrue($session->closed);
    }
}
