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
}
