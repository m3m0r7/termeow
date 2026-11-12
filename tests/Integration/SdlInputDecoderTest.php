<?php

declare(strict_types=1);

namespace Termeow\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Pnlx\Libsdl\Types\SDL_Event;
use Termeow\Infrastructure\Input\SdlInputDecoder;
use Termeow\Infrastructure\Pnl\NativeScalar;

use const Pnlx\Libsdl\SDL_KEYDOWN;
use const Pnlx\Libsdl\SDL_TEXTINPUT;
use const Pnlx\Libsdl\SDLK_LEFT;
use const Pnlx\Libsdl\SDLK_RETURN;

final class SdlInputDecoderTest extends TestCase
{
    public function testTextReturnAndControlKeys(): void
    {
        $text = new SDL_Event();
        $text->cdata()[0]->text->type = NativeScalar::int(SDL_TEXTINPUT);
        foreach (str_split('pwd') as $index => $character) {
            $text->cdata()[0]->text->text[$index] = $character;
        }
        self::assertSame('pwd', SdlInputDecoder::fromEvent($text));

        $key = new SDL_Event();
        $key->cdata()[0]->key->type = NativeScalar::int(SDL_KEYDOWN);
        $key->cdata()[0]->key->keysym->sym = NativeScalar::int(SDLK_RETURN);
        self::assertSame("\r", SdlInputDecoder::fromEvent($key));
        $key->cdata()[0]->key->keysym->mod = 0x0040;
        $key->cdata()[0]->key->keysym->sym = ord('c');
        self::assertSame("\x03", SdlInputDecoder::fromEvent($key));
        $key->cdata()[0]->key->keysym->mod = 0x0080;
        $key->cdata()[0]->key->keysym->sym = ord('k');
        self::assertSame("\x0b", SdlInputDecoder::fromEvent($key));
        $key->cdata()[0]->key->keysym->sym = NativeScalar::int(SDLK_LEFT);
        self::assertSame("\x1b[1;5D", SdlInputDecoder::fromEvent($key));
    }
}
