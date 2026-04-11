# CLAUDE.md

ProcessWire JSON API library for building structured API endpoints.
Requires PHP 8.2+, developed in a DDEV environment.
**MUST** maintain PHP 8.2 compatibility — do not use language features that require 8.3+.

## Commands

- `composer run test` — run tests (Pest PHP, requires DDEV)
- `composer run analyse` — static analysis (PHPStan level 9)
- `npm run format` — format code (Prettier)

## Dependencies

- **Pinned versions:** always install pnpm and Composer packages with exact versions (no `^` or `~` prefixes)

## Code conventions

- **Formatting:** Prettier with PHP plugin — 2 spaces, single quotes, trailing commas, 80-char line width
- **Internal API:** underscore prefix (`_prepare()`, `_isInitialized`), mark `@internal`
- **Fluent interface:** builder methods return `static`
- **Lazy init:** private `initXxx()` methods
- **Architecture:** trait-based composition with `Has...` naming (e.g. `HasServiceList`)
- **Naming:** `...Args` for DTOs, `...Exception`, `...Interface`, `...Config`
- **PHPDoc:** required for complex callables/generics; omit when native types suffice
- **@see links:** add `@see https://pwjsonapi.fokke.fi/...` to class-level PHPDoc when a matching documentation page or section exists. Verify link validity with curl or by inspecting the markdown source in `docs/`.
- **Control structures:** always use braces, no one-liner `if` statements
- **Array operations:** avoid `array_filter` + `array_map` combos — use `array_reduce` when both filtering and mapping are needed
- **Callbacks:** use `static fn`/`static function` for callbacks (e.g. `array_reduce`, `array_map`) that don't reference `$this`
- **Callback arguments:** hooks and other user-facing closures always receive a single DTO object (e.g. `AuthorizeArgs`, `RequestHookReturnBefore`) — never multiple arguments. This ensures extensibility without breaking changes.
- **Documentation code examples:** always use `function` for callbacks — no `fn` arrow functions or `static function`. Keep examples simple and consistent.

## Architecture

- **Service:** abstract class that groups endpoints; extends `Service`. Constructor sets base path and class properties. `init()` registers endpoints, hooks, and child services. This separation allows extending classes to override `init()` without inheriting parent endpoints.
- **Api:** top-level container that holds services, runs them via `run()`, and registers ProcessWire URL hooks.

## Testing

- Pest PHP framework
- Unit tests: `tests/Unit/`, feature tests: `tests/Feature/`
- Test helpers: `tests/Pest.php`

## Workflow checklist

When modifying code, always verify:

- When adding files or directories to the project root, ensure `.gitattributes` marks them `export-ignore` if they should not be included in the Composer package (only `src/`, `CHANGELOG.md`, `LICENSE`, `README.md`, and `composer.json` ship)
- The feature has an existing test, and the test is up to date with the change
- Documentation in `docs/` is up to date for the affected feature
- Each documentation page has a frontmatter `description` — a short, concise meta description for SEO
- When renaming a documentation file (which changes its URL), check all `docs/` files and `src/` `@see` links for references to the old path
- If the feature is not documented at all, ask the developer whether to add it
- After code changes, check modified files for unused variables and imports — remove them
- Document new features, bug fixes, and breaking changes in `CHANGELOG.md` under the next version number (determine from git tags — use `git ls-remote --tags origin` and treat existing CHANGELOG entries as unreleased). Use subsections matching existing entries: `### Breaking changes`, `### New features`, `### Bug fixes`. Do not document beta versions in the changelog.

## Git

- **Never commit** — only the developer commits. `git add` is allowed when requested. Suggest a commit message when pausing for review.
- Imperative mood, ~50 chars, no trailing period

## Multi-step plans

- Pause between phases so the developer can review, test, and commit each phase separately.
- Design phases to be independently committable — earlier phases must not depend on later ones.
