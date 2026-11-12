<?php

declare(strict_types=1);

namespace Termeow\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Pnlx\Libsdl\Types\SDL_Event;
use Termeow\Infrastructure\Sdl\Input\SdlKeyTranslator;
use Termeow\Infrastructure\Sdl\Input\SdlTextDecoder;
use Pnlx\Libsdl\Enums\SDL_EventType;
use Pnlx\Libsdl\Enums\SDL_KeyCode;
use Pnlx\Libsdl\Enums\SDL_WindowEventID;

final class SdlInputTranslationTest extends TestCase
{
    private const LEFT_CTRL = 0x0040;
    private const RIGHT_CTRL = 0x0080;
    private const LEFT_SHIFT = 0x0001;
    private const RIGHT_ALT = 0x0200;
    private const ALTGR_MODE = 0x4000;

    public function testTextReturnAndControlKeys(): void
    {
        $text = new SDL_Event();
        $text->cdata()[0]->text->type = SDL_EventType::SDL_TEXTINPUT->value;
        foreach (str_split('pwd') as $index => $character) {
            $text->cdata()[0]->text->text[$index] = $character;
        }
        self::assertSame('pwd', (new SdlTextDecoder())->committed($text));

        $key = new SDL_Event();
        $key->cdata()[0]->key->type = SDL_EventType::SDL_KEYDOWN->value;
        $key->cdata()[0]->key->keysym->sym = SDL_KeyCode::SDLK_RETURN->value;
        self::assertSame("\r", (new SdlKeyTranslator())->translate($key));
        $key->cdata()[0]->key->keysym->mod = 0x0040;
        $key->cdata()[0]->key->keysym->sym = ord('c');
        self::assertSame("\x03", (new SdlKeyTranslator())->translate($key));
        $key->cdata()[0]->key->keysym->mod = 0x0080;
        $key->cdata()[0]->key->keysym->sym = ord('k');
        self::assertSame("\x0b", (new SdlKeyTranslator())->translate($key));
        $key->cdata()[0]->key->keysym->sym = SDL_KeyCode::SDLK_LEFT->value;
        self::assertSame("\x1b[1;5D", (new SdlKeyTranslator())->translate($key));
    }

    public function testAllAsciiControlCharactersAndDelete(): void
    {
        $translator = new SdlKeyTranslator();
        $event = new SDL_Event();
        $event->setType(SDL_EventType::SDL_KEYDOWN->value);
        $event->getKey()->getKeysym()->setMod(self::LEFT_CTRL);

        $controls = [SDL_KeyCode::SDLK_SPACE->value => 0];
        for ($index = 0; $index < 26; $index++) {
            $controls[ord('a') + $index] = $index + 1;
        }
        $controls += [
            SDL_KeyCode::SDLK_LEFTBRACKET->value => 27,
            SDL_KeyCode::SDLK_BACKSLASH->value => 28,
            SDL_KeyCode::SDLK_RIGHTBRACKET->value => 29,
            SDL_KeyCode::SDLK_CARET->value => 30,
            SDL_KeyCode::SDLK_UNDERSCORE->value => 31,
            SDL_KeyCode::SDLK_QUESTION->value => 127,
        ];
        foreach ($controls as $keycode => $byte) {
            $event->getKey()->getKeysym()->setSym($keycode);
            self::assertSame(chr($byte), $translator->translate($event), sprintf('Ctrl+ keycode %d', $keycode));
        }

        $aliases = [
            SDL_KeyCode::SDLK_2->value => 0,
            SDL_KeyCode::SDLK_3->value => 27,
            SDL_KeyCode::SDLK_4->value => 28,
            SDL_KeyCode::SDLK_5->value => 29,
            SDL_KeyCode::SDLK_6->value => 30,
            SDL_KeyCode::SDLK_7->value => 31,
            SDL_KeyCode::SDLK_MINUS->value => 31,
            SDL_KeyCode::SDLK_8->value => 127,
        ];
        $event->getKey()->getKeysym()->setMod(self::RIGHT_CTRL);
        foreach ($aliases as $keycode => $byte) {
            $event->getKey()->getKeysym()->setSym($keycode);
            self::assertSame(chr($byte), $translator->translate($event), sprintf('Ctrl+ keycode %d', $keycode));
        }

        $event->getKey()->getKeysym()->setSym(SDL_KeyCode::SDLK_SLASH->value);
        $event->getKey()->getKeysym()->setMod(self::RIGHT_CTRL | self::LEFT_SHIFT);
        self::assertSame("\x7f", $translator->translate($event));
    }

    public function testAltGrDoesNotBecomeControlInput(): void
    {
        $translator = new SdlKeyTranslator();
        $event = new SDL_Event();
        $event->setType(SDL_EventType::SDL_KEYDOWN->value);
        $event->getKey()->getKeysym()->setSym(SDL_KeyCode::SDLK_q->value);
        foreach ([self::RIGHT_ALT, self::ALTGR_MODE] as $modifier) {
            $event->getKey()->getKeysym()->setMod(self::LEFT_CTRL | $modifier);
            self::assertNull($translator->translate($event));
        }
    }
}
