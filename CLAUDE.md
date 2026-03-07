# CLAUDE.md

ProcessWire JSON API library for building structured API endpoints.
Requires PHP 8.2+, developed in a DDEV environment.

## Commands

- `composer run test` — run tests (Pest PHP, requires DDEV)
- `composer run analyse` — static analysis (PHPStan level 9)
- `npm run format` — format code (Prettier)

## Code conventions

- **Formatting:** Prettier with PHP plugin — 2 spaces, single quotes, trailing commas, 80-char line width
- **Internal API:** underscore prefix (`_prepare()`, `_isInitialized`), mark `@internal`
- **Fluent interface:** builder methods return `static`
- **Lazy init:** private `initXxx()` methods
- **Architecture:** trait-based composition with `Has...` naming (e.g. `HasServiceList`)
- **Naming:** `...Args` for DTOs, `...Exception`, `...Interface`, `...Config`
- **PHPDoc:** required for complex callables/generics; omit when native types suffice

## Testing

- Pest PHP framework
- Unit tests: `tests/Unit/`, feature tests: `tests/Feature/`
- Test helpers: `tests/Pest.php`

## Git

- Imperative mood, ~50 chars, no trailing period
