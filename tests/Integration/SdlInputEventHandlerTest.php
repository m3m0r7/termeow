<?php

declare(strict_types=1);

namespace Termeow\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Pnlx\Libsdl\Libsdl as SDL;
use Pnlx\Libsdl\Types\SDL_Event;
use Termeow\Infrastructure\Sdl\Input\SdlInputEventHandler;
use Termeow\Infrastructure\Sdl\Input\SdlKeyTranslator;
use Termeow\Infrastructure\Sdl\Input\SdlTextDecoder;
use Pnlx\Libsdl\Enums\SDL_EventType;
use Pnlx\Libsdl\Enums\SDL_KeyCode;
use Pnlx\Libsdl\Enums\SDL_WindowEventID;

final class SdlInputEventHandlerTest extends TestCase
{
    private const LEFT_CTRL = 0x0040;

    public function testJapaneseCompositionDoesNotSubmitUntilCommitted(): void
    {
        $handler = new SdlInputEventHandler(new SdlKeyTranslator(), new SdlTextDecoder());
        $editing = new SDL_Event();
        $editing->setType(SDL_EventType::SDL_TEXTEDITING->value);
        $this->setText($editing->cdata()[0]->edit->text, 'にほん');
        $editing->getEdit()->setStart(3);
        $actions = iterator_to_array($handler->handle($editing));
        self::assertSame('にほん', $actions[0]->composition?->text());
        self::assertSame('', $actions[0]->bytes);

        $return = new SDL_Event();
        $return->setType(SDL_EventType::SDL_KEYDOWN->value);
        $return->getKey()->getKeysym()->setSym(SDL_KeyCode::SDLK_RETURN->value);
        self::assertSame([], iterator_to_array($handler->handle($return)));

        $control = new SDL_Event();
        $control->setType(SDL_EventType::SDL_KEYDOWN->value);
        $control->getKey()->getKeysym()->setSym(SDL_KeyCode::SDLK_RIGHTBRACKET->value);
        $control->getKey()->getKeysym()->setMod(self::LEFT_CTRL);
        self::assertSame([], iterator_to_array($handler->handle($control)));

        $committed = new SDL_Event();
        $committed->setType(SDL_EventType::SDL_TEXTINPUT->value);
        $this->setText($committed->cdata()[0]->text->text, '日本');
        $actions = iterator_to_array($handler->handle($committed));
        self::assertSame('', $actions[0]->composition?->text());
        self::assertSame('日本', $actions[1]->bytes);
        $returnBytes = '';
        foreach ($handler->handle($return) as $action) {
            $returnBytes .= $action->bytes;
        }
        self::assertSame("\r", $returnBytes);
        $controlBytes = '';
        foreach ($handler->handle($control) as $action) {
            $controlBytes .= $action->bytes;
        }
        self::assertSame("\x1d", $controlBytes);
    }

    public function testLongCompositionAndChunkedCommit(): void
    {
        $handler = new SdlInputEventHandler(new SdlKeyTranslator(), new SdlTextDecoder());
        $text = '日本語の長い変換文字列を確認する';
        $memory = SDL::SDL_malloc(strlen($text) + 1);
        self::assertNotNull($memory);
        \FFI::memcpy($memory->cdata(), $text . "\0", strlen($text) + 1);
        $editing = new SDL_Event();
        $editing->setType(SDL_EventType::SDL_TEXTEDITING_EXT->value);
        $editing->cdata()[0]->editExt->text = $memory->cdata();
        $editing->getEditExt()->setStart(5);
        $actions = iterator_to_array($handler->handle($editing));
        self::assertSame($text, $actions[0]->composition?->text());
        self::assertSame(5, $actions[0]->composition->cursor());

        $committed = '';
        foreach ([mb_substr($text, 0, 8), mb_substr($text, 8)] as $chunk) {
            $event = new SDL_Event();
            $event->setType(SDL_EventType::SDL_TEXTINPUT->value);
            $this->setText($event->cdata()[0]->text->text, $chunk);
            foreach ($handler->handle($event) as $action) {
                $committed .= $action->bytes;
            }
        }
        self::assertSame($text, $committed);
    }

    public function testFocusLossClearsComposition(): void
    {
        $handler = new SdlInputEventHandler(new SdlKeyTranslator(), new SdlTextDecoder());
        $editing = new SDL_Event();
        $editing->setType(SDL_EventType::SDL_TEXTEDITING->value);
        $this->setText($editing->cdata()[0]->edit->text, 'に');
        iterator_to_array($handler->handle($editing));

        $focus = new SDL_Event();
        $focus->setType(SDL_EventType::SDL_WINDOWEVENT->value);
        $focus->getWindow()->setEvent(SDL_WindowEventID::SDL_WINDOWEVENT_FOCUS_LOST->value);
        $actions = iterator_to_array($handler->handle($focus));
        self::assertSame('', $actions[0]->composition?->text());

        $empty = new SDL_Event();
        $empty->setType(SDL_EventType::SDL_TEXTEDITING->value);
        $empty->getEdit()->setStart(3);
        $actions = iterator_to_array($handler->handle($empty));
        self::assertSame('', $actions[0]->composition?->text());
    }

    private function setText(mixed $buffer, string $text): void
    {
        if (!$buffer instanceof \FFI\CData || strlen($text) >= 32) {
            throw new \InvalidArgumentException('Invalid SDL text buffer');
        }
        \FFI::memcpy($buffer, $text . "\0", strlen($text) + 1);
    }
}
