# Architecture

Termeow is a Composer application (`type: project`) with `bin/termeow` exposed to consumers as `vendor/bin/termeow`.

## Boundaries

- `Domain` owns terminal cells, colors, cursor positions, dimensions, and their contracts.
- `Application` owns the terminal loop and ports. It imports no infrastructure type.
- `Infrastructure/Emulation` owns the libvterm screen, palette installation, cell reading, and their factories.
- Libvterm-generated terminal responses are read through an application port and written back to the PTY after shell output is parsed. This includes DSR replies used by Neovim at startup.
- `Infrastructure/Sdl` owns SDL input and rendering. Its runtime, font set, window, terminal grid painter, glyph painter, composition painter, bitmap exporter, and factories stay in this module.
- `Infrastructure/Process` owns PTY startup arguments, environment, process lifecycle, input writing, output reading, and session creation.
- `Infrastructure/Config` owns TOML layering, font resolution, and settings creation.
- `Infrastructure/Pnl` loads generated native bindings when a terminal is launched.
- `Infrastructure/Composition/TermeowTerminalFactory` assembles screen, renderer, session, and input through application ports only. `bin/termeow` is the sole composition root that selects concrete adapters.

Every production class implements an `Interface` contract. Public constructors depend on interfaces; immutable leaf values accept PHP scalars through named factories. Mutable state is private and changed through methods. Each adapter owns its exceptions. Architecture tests reject imports across adapter modules, native binding imports outside their adapters, public scalar constructors, missing class interfaces, and `final readonly` classes.

## Configuration

`termeow.toml` is loaded in this order:

1. Package default (required)
2. `~/termeow.toml` (optional)
3. `<cwd>/termeow.toml` (optional)

TOML tables merge recursively. A later value overrides the same key without replacing unrelated keys. A relative font path uses the directory of the file that last defined that font key. The environment and startup script are injected into the PTY adapter through contracts.

## Verification

`composer check` runs PHPUnit, PHPStan at max level, and PHP_CodeSniffer. Integration tests render SDL bitmap output and exercise a real PTY with `pwd`, `ls`, version commands, UTF-8, C0/DEL control bytes, and `sl` when installed. SDL key translation covers the full C0 range and common aliases while preserving AltGr text input. The SDL renderer also handles IME composition without sending uncommitted text to the shell.
