<?php

declare(strict_types=1);

namespace Termeow\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Pnlx\Libsdl\Libsdl as SDL;
use Pnlx\Libsdl\Types\SDL_Event;
use Termeow\Infrastructure\Input\SdlInputEventHandler;
use Termeow\Infrastructure\Pnl\NativeScalar;

use const Pnlx\Libsdl\SDL_KEYDOWN;
use const Pnlx\Libsdl\SDL_TEXTEDITING;
use const Pnlx\Libsdl\SDL_TEXTEDITING_EXT;
use const Pnlx\Libsdl\SDL_TEXTINPUT;
use const Pnlx\Libsdl\SDL_WINDOWEVENT;
use const Pnlx\Libsdl\SDL_WINDOWEVENT_FOCUS_LOST;
use const Pnlx\Libsdl\SDLK_RETURN;

final class SdlInputEventHandlerTest extends TestCase
{
    public function testJapaneseCompositionDoesNotSubmitUntilCommitted(): void
    {
        $handler = new SdlInputEventHandler();
        $editing = new SDL_Event();
        $editing->setType(NativeScalar::int(SDL_TEXTEDITING));
        $this->setText($editing->cdata()[0]->edit->text, 'にほん');
        $editing->getEdit()->setStart(3);
        $actions = iterator_to_array($handler->handle($editing));
        self::assertSame('にほん', $actions[0]->composition?->text);
        self::assertSame('', $actions[0]->bytes);

        $return = new SDL_Event();
        $return->setType(NativeScalar::int(SDL_KEYDOWN));
        $return->getKey()->getKeysym()->setSym(NativeScalar::int(SDLK_RETURN));
        self::assertSame([], iterator_to_array($handler->handle($return)));

        $committed = new SDL_Event();
        $committed->setType(NativeScalar::int(SDL_TEXTINPUT));
        $this->setText($committed->cdata()[0]->text->text, '日本');
        $actions = iterator_to_array($handler->handle($committed));
        self::assertSame('', $actions[0]->composition?->text);
        self::assertSame('日本', $actions[1]->bytes);
        $returnBytes = '';
        foreach ($handler->handle($return) as $action) {
            $returnBytes .= $action->bytes;
        }
        self::assertSame("\r", $returnBytes);
    }

    public function testLongCompositionAndChunkedCommit(): void
    {
        $handler = new SdlInputEventHandler();
        $text = '日本語の長い変換文字列を確認する';
        $memory = SDL::SDL_malloc(strlen($text) + 1);
        self::assertNotNull($memory);
        \FFI::memcpy($memory->cdata(), $text . "\0", strlen($text) + 1);
        $editing = new SDL_Event();
        $editing->setType(NativeScalar::int(SDL_TEXTEDITING_EXT));
        $editing->cdata()[0]->editExt->text = $memory->cdata();
        $editing->getEditExt()->setStart(5);
        $actions = iterator_to_array($handler->handle($editing));
        self::assertSame($text, $actions[0]->composition?->text);
        self::assertSame(5, $actions[0]->composition->cursor);

        $committed = '';
        foreach ([mb_substr($text, 0, 8), mb_substr($text, 8)] as $chunk) {
            $event = new SDL_Event();
            $event->setType(NativeScalar::int(SDL_TEXTINPUT));
            $this->setText($event->cdata()[0]->text->text, $chunk);
            foreach ($handler->handle($event) as $action) {
                $committed .= $action->bytes;
            }
        }
        self::assertSame($text, $committed);
    }

    public function testFocusLossClearsComposition(): void
    {
        $handler = new SdlInputEventHandler();
        $editing = new SDL_Event();
        $editing->setType(NativeScalar::int(SDL_TEXTEDITING));
        $this->setText($editing->cdata()[0]->edit->text, 'に');
        iterator_to_array($handler->handle($editing));

        $focus = new SDL_Event();
        $focus->setType(NativeScalar::int(SDL_WINDOWEVENT));
        $focus->getWindow()->setEvent(NativeScalar::int(SDL_WINDOWEVENT_FOCUS_LOST));
        $actions = iterator_to_array($handler->handle($focus));
        self::assertSame('', $actions[0]->composition?->text);

        $empty = new SDL_Event();
        $empty->setType(NativeScalar::int(SDL_TEXTEDITING));
        $empty->getEdit()->setStart(3);
        $actions = iterator_to_array($handler->handle($empty));
        self::assertSame('', $actions[0]->composition?->text);
    }

    private function setText(mixed $buffer, string $text): void
    {
        if (!$buffer instanceof \FFI\CData || strlen($text) >= 32) {
            throw new \InvalidArgumentException('Invalid SDL text buffer');
        }
        \FFI::memcpy($buffer, $text . "\0", strlen($text) + 1);
    }
}
