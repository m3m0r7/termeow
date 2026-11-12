# Termeow

Termeow is a small terminal application written in PHP. SDL2 and SDL_ttf render the window, libvterm interprets terminal output, and a controlling pseudo terminal runs an interactive shell. Native libraries are accessed through [pnl](https://github.com/m3m0r7/pnl).

## Requirements

- PHP 8.3 or newer with FFI and mbstring
- Composer
- macOS or Linux with a desktop display
- SDL2, SDL2_ttf, and libvterm libraries and headers
- `/usr/bin/script` and a monospace font
- Fontconfig (`fc-match`) when configuring fonts by family name

Install native libraries on macOS with Homebrew:

```sh
brew install sdl2 sdl2_ttf libvterm
```

On Debian or Ubuntu:

```sh
sudo apt install libsdl2-dev libsdl2-ttf-dev libvterm-dev php-ffi php-mbstring
```

## Quick start

From this repository:

```sh
composer install
vendor/bin/pnl install
bin/termeow
```

When installed as an application dependency, Composer creates `vendor/bin/termeow` in the consuming project:

```sh
composer require m3m0r7/termeow
(cd vendor/m3m0r7/termeow && ../../bin/pnl install)
vendor/bin/termeow
```

Pnl generates platform-specific bindings in the installed Termeow package. Repeat `pnl install` after replacing the package or changing the native platform. Composer does not run dependency scripts during installation. The command starts the shell in the caller's current directory.

Type `pwd` or `ls` and press Enter. `sl` works when it is installed on `PATH`. Ctrl+C interrupts the foreground job; Ctrl+K and the other supported control keys are passed to the shell. Close the window or type `exit` to quit.

Japanese input uses the system IME. Composition text appears underlined at the terminal cursor, with the native candidate window nearby. Only committed UTF-8 text is sent to the shell; keys used while composing do not submit a command.

`termeow -v` and `termeow --version` print the Termeow version, PHP runtime version, and credits. Inside the Termeow shell, entering `termeow` shows the same information. The `termoew` alias is available only inside that shell. The child shell receives `TERM_PROGRAM=termeow` and `TERM_PROGRAM_VERSION`.

## Configuration

Edit the package's `termeow.toml` to choose the font, fallback font, text size, window title, and ANSI colors:

```toml
[font]
family = "Source Code Pro for Powerline"
fallback = "Source Han Code JP"
size = 11
antialias = false

[window]
title = "Termeow"
```

The primary font must have equal-width ASCII glyphs. The fallback font supplies missing CJK and Powerline symbols. Use installed family names or absolute/configuration-relative font paths. The `[colors.primary]`, `[colors.normal]`, and `[colors.bright]` tables contain `#RRGGBB` values. ANSI colors 0–15 use the configured palette; indexed and true-color escapes are also rendered. The window uses the system title bar and an opaque background.

## Development

The code is split into terminal values (`src/Domain`), the terminal loop and ports (`src/Application`), and SDL, libvterm, PTY, input, and configuration adapters (`src/Infrastructure`). The application layer does not depend on pnl types.

```sh
composer test   # PHPUnit unit and integration tests
composer stan   # PHPStan level max
composer cs     # PHP_CodeSniffer, PSR-12
composer check  # all three checks
```

The PHPUnit integration suite uses SDL's dummy video driver to check rendered bitmap pixels, including Japanese composition. It also starts a real shell through `/usr/bin/script` to exercise `pwd`, `ls`, Japanese UTF-8 output, version commands, Ctrl+C, and Ctrl+K. If `sl` is available, the suite checks its animation output. Native bindings must be generated before running the suite.

## License

MIT. See [LICENSE](LICENSE).
