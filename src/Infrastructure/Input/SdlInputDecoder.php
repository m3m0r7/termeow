<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Input;

use Pnlx\Libsdl\Libsdl as SDL;
use Pnlx\Libsdl\LibsdlContext;
use Pnlx\Libsdl\Types\SDL_Event;
use Termeow\Application\TextComposition;
use Termeow\Infrastructure\Pnl\NativeScalar;

use const Pnlx\Libsdl\SDL_KEYDOWN;
use const Pnlx\Libsdl\SDL_TEXTEDITING;
use const Pnlx\Libsdl\SDL_TEXTEDITING_EXT;
use const Pnlx\Libsdl\SDL_TEXTINPUT;
use const Pnlx\Libsdl\SDLK_BACKSPACE;
use const Pnlx\Libsdl\SDLK_DELETE;
use const Pnlx\Libsdl\SDLK_DOWN;
use const Pnlx\Libsdl\SDLK_END;
use const Pnlx\Libsdl\SDLK_ESCAPE;
use const Pnlx\Libsdl\SDLK_HOME;
use const Pnlx\Libsdl\SDLK_LEFT;
use const Pnlx\Libsdl\SDLK_LEFTBRACKET;
use const Pnlx\Libsdl\SDLK_BACKSLASH;
use const Pnlx\Libsdl\SDLK_RIGHTBRACKET;
use const Pnlx\Libsdl\SDLK_SPACE;
use const Pnlx\Libsdl\SDLK_RETURN;
use const Pnlx\Libsdl\SDLK_RIGHT;
use const Pnlx\Libsdl\SDLK_TAB;
use const Pnlx\Libsdl\SDLK_UP;

final class SdlInputDecoder
{
    private const CTRL_MASK = 0x00c0;

    public static function fromEvent(SDL_Event $event): ?string
    {
        $type = $event->getType();
        if ($type === NativeScalar::int(SDL_TEXTINPUT)) {
            return self::readBuffer($event->cdata()[0]->text->text);
        }
        if ($type !== NativeScalar::int(SDL_KEYDOWN)) {
            return null;
        }
        $keysym = $event->getKey()->getKeysym();
        $key = $keysym->getSym();
        $modifiers = $keysym->getMod();
        if (($modifiers & self::CTRL_MASK) !== 0) {
            if ($key >= ord('a') && $key <= ord('z')) {
                return chr(($key - ord('a') + 1) & 0xff);
            }
            $control = match ($key) {
                NativeScalar::int(SDLK_SPACE), ord('2'), ord('@') => "\x00",
                NativeScalar::int(SDLK_LEFTBRACKET), ord('3') => "\x1b",
                NativeScalar::int(SDLK_BACKSLASH), ord('4') => "\x1c",
                NativeScalar::int(SDLK_RIGHTBRACKET), ord('5') => "\x1d",
                ord('6'), ord('^') => "\x1e",
                ord('7'), ord('_'), ord('-') => "\x1f",
                ord('8'), ord('?') => "\x7f",
                NativeScalar::int(SDLK_LEFT) => "\x1b[1;5D",
                NativeScalar::int(SDLK_RIGHT) => "\x1b[1;5C",
                NativeScalar::int(SDLK_UP) => "\x1b[1;5A",
                NativeScalar::int(SDLK_DOWN) => "\x1b[1;5B",
                default => null,
            };
            if ($control !== null) {
                return $control;
            }
        }
        return match ($key) {
            NativeScalar::int(SDLK_RETURN) => "\r",
            NativeScalar::int(SDLK_BACKSPACE) => "\x7f",
            NativeScalar::int(SDLK_TAB) => "\t",
            NativeScalar::int(SDLK_ESCAPE) => "\x1b",
            NativeScalar::int(SDLK_UP) => "\x1b[A",
            NativeScalar::int(SDLK_DOWN) => "\x1b[B",
            NativeScalar::int(SDLK_RIGHT) => "\x1b[C",
            NativeScalar::int(SDLK_LEFT) => "\x1b[D",
            NativeScalar::int(SDLK_HOME) => "\x1b[H",
            NativeScalar::int(SDLK_END) => "\x1b[F",
            NativeScalar::int(SDLK_DELETE) => "\x1b[3~",
            default => null,
        };
    }

    public static function compositionFromEvent(SDL_Event $event): ?TextComposition
    {
        if ($event->getType() === NativeScalar::int(SDL_TEXTEDITING)) {
            $editing = $event->getEdit();
            $text = self::readBuffer($event->cdata()[0]->edit->text);
            if ($text === '') {
                return TextComposition::empty();
            }
            return new TextComposition(
                $text,
                $editing->getStart(),
                $editing->getLength(),
            );
        }
        if ($event->getType() !== NativeScalar::int(SDL_TEXTEDITING_EXT)) {
            return null;
        }
        $editing = $event->getEditExt();
        $text = $editing->getText() ?? '';
        $pointer = $event->cdata()[0]->editExt->text;
        // 拡張変換イベントの文字列は受信側が所有し、コピー後に解放する。
        if ($pointer instanceof \FFI\CData && !\Pnlx\Util::isNull($pointer)) {
            SDL::SDL_free(new LibsdlContext($pointer));
        }
        if ($text === '') {
            return TextComposition::empty();
        }
        return new TextComposition($text, $editing->getStart(), $editing->getLength());
    }

    private static function readBuffer(mixed $buffer): string
    {
        if (!$buffer instanceof \FFI\CData) {
            throw new \RuntimeException('SDL text buffer is unavailable');
        }
        $raw = \FFI::string($buffer, 32);
        $end = strpos($raw, "\0");
        return $end === false ? $raw : substr($raw, 0, $end);
    }
}
