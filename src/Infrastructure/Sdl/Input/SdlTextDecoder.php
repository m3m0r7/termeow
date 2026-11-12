<?php

declare(strict_types=1);

namespace Termeow\Infrastructure\Sdl\Input;

use Pnlx\Libsdl\Enums\SDL_EventType;
use Pnlx\Libsdl\Libsdl as SDL;
use Pnlx\Libsdl\LibsdlContext;
use Pnlx\Libsdl\Types\SDL_Event;
use Termeow\Application\TextComposition;
use Termeow\Infrastructure\Sdl\Exception\InputDecodingException;

readonly class SdlTextDecoder implements SdlTextDecoderInterface
{
    private const TEXT_BUFFER_BYTES = 32;

    public function committed(SDL_Event $event): ?string
    {
        if ($event->getType() !== SDL_EventType::SDL_TEXTINPUT->value) {
            return null;
        }
        return $this->readBuffer($event->cdata()[0]->text->text);
    }

    public function composition(SDL_Event $event): ?TextComposition
    {
        if ($event->getType() === SDL_EventType::SDL_TEXTEDITING->value) {
            $editing = $event->getEdit();
            $text = $this->readBuffer($event->cdata()[0]->edit->text);
            if ($text === '') {
                return TextComposition::empty();
            }
            return TextComposition::of($text, $editing->getStart(), $editing->getLength());
        }
        if ($event->getType() !== SDL_EventType::SDL_TEXTEDITING_EXT->value) {
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
        return TextComposition::of($text, $editing->getStart(), $editing->getLength());
    }

    private function readBuffer(mixed $buffer): string
    {
        if (!$buffer instanceof \FFI\CData) {
            throw new InputDecodingException('SDL text buffer is unavailable');
        }
        $raw = \FFI::string($buffer, self::TEXT_BUFFER_BYTES);
        $end = strpos($raw, "\0");
        return $end === false ? $raw : substr($raw, 0, $end);
    }
}
