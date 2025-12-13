# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working
with the Aphiria framework repository.

## Project Overview

- Aphiria is a PHP REST API framework organized as a monorepo.
- The repository contains multiple decoupled libraries under `src/{Library}`.
- Each library contains its own `src/`, `tests/`, and `composer.json`.
- PHP 8.4 or greater is required.
- Namespaces follow `Aphiria\{Library}\`.
- Test namespaces append `Tests`.

Libraries include:

- Application
- Api
- Authentication
- Authorization
- Collections
- Console
- ContentNegotiation
- DependencyInjection
- Exceptions
- Framework
- IO
- Middleware
- Net
- PsrAdapters
- Reflection
- Router
- Security
- Sessions
- Validation

## Specs

- Feature specifications live in the `/.specs` directory.
- Specs are authoritative for feature behavior.
- When a spec exists, follow it exactly.
- Do not infer requirements outside the spec.
- If a spec is unclear or incomplete, ask before implementing.

## High-Level Principles

- Aphiria favors explicit configuration over implicit behavior.
- The framework is designed primarily for REST APIs.
- Do not assume Laravel, Symfony, or other framework conventions.
- Prefer small, explicit changes over large refactors.
- Respect library boundaries in the monorepo.
- We must maintain 100% unit test coverage - anything less is unacceptable.
- Any new files added to the repository must be added to git, too.

## Development Workflow

### Composer Scripts

The repository defines standard Composer scripts. Prefer using these
instead of invoking tools directly.

Common scripts include:

- `composer test`
    - Runs the full test suite, including PHPUnit, code style checks,
      and static analysis.
- `composer phpunit`
    - Runs PHPUnit tests only.
- `composer psalm`
    - Runs static analysis.
- `composer phpcs-test`
    - Runs PHP-CS-Fixer in dry-run mode.
- `composer phpcs-fix`
    - Applies PHP-CS-Fixer fixes.

Claude should recommend these scripts when suggesting tests or checks.

### Testing

- Tests live in `src/*/tests`.
- PHPUnit is used for unit and integration testing.
- Authentication and services are designed to be mockable via DI.
- New behavior should include tests when appropriate.

## Coding Standards

### PHP-CS-Fixer

- PHP-CS-Fixer enforces the canonical coding standard.
- The ruleset is defined in `.php-cs-fixer.dist.php`.
- The standard is based on PER-CS2 with Aphiria-specific rules.

Key enforced concepts:

- `strict_types=1` is required in all PHP files.
- A standard Aphiria file header is required.
- Native function calls are fully qualified where applicable.
- Import ordering and class member ordering are enforced.
- Single quotes are preferred where possible.
- Unused imports are not allowed.
- Formatting rules are strict and should not be manually overridden.

Do not manually reformat unrelated code. Use the fixer.

## Architecture Overview

### Application Composition

- Applications are composed from modules and components.
- Modules group related functionality and may register:
    - components
    - DI binders
    - routes
    - middleware
    - console commands
    - authentication schemes
- Application builders manage module registration and build order.

### Bootstrappers

- Bootstrappers run before application builders.
- They are responsible for minimal early setup such as loading config.

### Component Discovery

- Aphiria includes an automatic component discovery system.
- Discovery scans configured paths and finds discoverers and builders.
- Discovered components are built via corresponding builders.
- Discovery is opt-in and explicitly configured.

## Dependency Injection

- Aphiria uses a constructor-based DI container.
- Bindings must be explicit.
- Supported binding types:
    - instance
    - factory
    - class (auto-wired)
- Auto-wiring uses constructor type hints recursively.
- Primitive values are not auto-wired.
- Targeted bindings allow different implementations per consuming class.

Do not assume implicit resolution of untyped dependencies.

## Routing and Controllers

- Controllers are used for API endpoints.
- Routes are defined via PHP attributes.
- Controller methods may receive:
    - route variables
    - query values
    - deserialized request bodies
- Controllers return response objects or values that are negotiated.

Controllers are API-focused, not MVC view controllers.

## Content Negotiation

- Content negotiation is automatic.
- Request bodies are deserialized based on Content-Type.
- Responses are serialized based on Accept headers.
- JSON, XML, HTML, and plain text formatters are supported.
- Custom formatters may be registered explicitly.

Do not manually serialize responses unless necessary.

## Authentication

- Authentication is scheme-based and explicit.
- Schemes are identified by name and implemented by handlers.
- Routes are protected using the `#[Authenticate]` attribute.
- Authentication succeeds if any specified scheme succeeds.
- Authentication produces a principal attached to the request.
- Authentication does not imply authorization.

Do not assume a default user model or credential store.

## Authorization

- Authorization is policy-based.
- Policies consist of one or more requirements.
- Authorization is applied via attributes or `IAuthority`.
- Resource-based authorization may inspect both principal and resource.
- Authorization is separate from authentication.

Permissions are never inferred automatically.

## Console

- Console commands are implemented via `ICommandHandler`.
- Commands may be registered manually or via attributes.
- Commands integrate with dependency injection.
- Built-in commands may be enabled or disabled by modules.

Console behavior should remain within the Console library.

## Collections

- Aphiria provides collection types such as lists, hash tables,
  sets, stacks, queues, and immutable variants.
- Collections provide object semantics beyond native PHP arrays.
- Collections may be converted to arrays when needed.

## Development Guardrails

- Do not introduce breaking API changes without tests and versioning.
- Do not refactor unrelated code by default.
- Keep diffs minimal.
- Respect existing architecture and library boundaries.

## AI-Specific Guidance

- Always prefer the smallest change that satisfies the request.
- Do not assume undocumented behavior.
- Ask before making architectural changes.
- Re-read files from disk if changes are reverted or unclear.
- Specs and code are the source of truth, not memory.
