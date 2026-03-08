# CLAUDE.md

ProcessWire JSON API library for building structured API endpoints.
Requires PHP 8.2+, developed in a DDEV environment.
**MUST** maintain PHP 8.2 compatibility — do not use language features that require 8.3+.

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
- **Control structures:** always use braces, no one-liner `if` statements

## Architecture

- **Service:** abstract class that groups endpoints; extends `Service` and overrides `init()` to add endpoints, hooks, and child services. `init()` is the preferred place for all service configuration.
- **Api:** top-level container that holds services, runs them via `run()`, and registers ProcessWire URL hooks.

## Testing

- Pest PHP framework
- Unit tests: `tests/Unit/`, feature tests: `tests/Feature/`
- Test helpers: `tests/Pest.php`

## Workflow checklist

When modifying code, always verify:

- The feature has an existing test, and the test is up to date with the change
- Documentation in `docs/` is up to date for the affected feature
- If the feature is not documented at all, ask the developer whether to add it

## Git

- Imperative mood, ~50 chars, no trailing period
