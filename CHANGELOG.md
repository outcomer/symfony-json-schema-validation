# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [4.0.0] - 2026-09-02

### Added
- `schemas` configuration option - an array of `{path, domain}` pairs, replacing the single `schemas_path`/`schema_domain` pair so multiple schema directories (each under their own domain) can be registered at once
- `SchemaValidator::registerSchemaDir(domain, path)` - public API to register an additional schema directory on the shared resolver after construction; used internally to register the bundle's own `Examples/Schemas/` (and its `common/` subdirectory) without colliding with the app's own `schemas_path`
- `auto_cast_query` and `auto_cast_path` configuration options (default `true`) to control automatic numeric/boolean casting for query and path parameters independently
- `HttpValidationException` - HTTP-transport wrapper around the domain `ValidationException`, thrown by `MapRequestResolver` when `#[MapRequest]`'s `triggerResponse` is `true`
- `SchemaFilterResolver` - extracted service that recursively discovers `$func` filter names declared in a schema's `$filters`
- `Helpers\Arrays::toObjectGraph()` - the array-to-stdClass-graph conversion previously inlined in `SchemaValidator`
- Opis `$filters` example (`PromoCodeFilter`) wired into the bundled `/_examples/validation/user` route, including a custom `$error` message
- Reusable schema `$ref` examples: `name.json`, `email.json`, `age.json` extracted from `user-create.json`
- New `/_examples/validation/order` example route and schema set (`order-create.json`, `customer.json`, `order-item.json`, `common/address.json`, `common/country.json`) demonstrating multi-level nesting with both relative and absolute `$ref`
- Application-level end-to-end tests exercising the bundle's example routes through a real host application kernel and router (replacing the previous bundle-only synthetic kernel tests)
- Mermaid diagrams in the documentation: a sequence diagram of the `#[MapRequest]` request/validation/DTO flow, and a dependency graph of the example schemas' `$ref` relationships
- `.github/FUNDING.yml` and a GitHub Sponsors button in the documentation nav bar

### Changed
- **`ValidationException` is now a plain domain exception** (`extends RuntimeException`, no longer `extends HttpException`) - HTTP concerns are handled exclusively by the new `HttpValidationException`
- `SchemaValidator` now builds one `SchemaLoader`/`SchemaResolver` per instance and reuses it across calls, instead of a fresh `Validator` (and its underlying schema cache) on every single validation - Opis parses each schema file only once per loader, so long-lived processes (e.g. Swoole/RoadRunner workers) no longer pay the full parse cost on every request. Filters are still re-registered fresh on every call, since a filter's resolved service may not be shared
- Headers are never auto-cast (previously could be affected by global type casting)

### Deprecated
- `schemas_path` and `schema_domain` configuration options - use `schemas` instead. They still work but emit a deprecation notice

### Removed
- Dead code in `Helpers\Arrays`: `insertInArray`, `toObject`, `groupBy`, `arrayReplaceKeys` (only `sortArrayByKeys` remains, in use)
- Synthetic bundle-only `TestKernel` and its E2E test suite - replaced by application-level E2E tests (see Added)

### Fixed
- Multiple documentation inaccuracies found to not match the actual implementation: fabricated `ValidatedRequest`/`ValidatedPayload` API in api.md (real `ValidatedRequest` only exposes `getPayload(): Payload`, plus `getViolations()`/`hasViolations()`/`isValid()`/`getStatus()`; `ValidatedPayload` does not exist - the real class is `Payload`), `ValidatedDtoInterface` incorrectly shown as an empty marker interface, fabricated `TrimFilter`/`LowercaseFilter` classes, wrong PHP/Symfony version requirements (was showing PHP 8.4+/Symfony 8.0+; real requirement is PHP >=8.2, Symfony ^7.4|^8.0), wrong `schema_domain` default (was documented as `null`, actually `https://outcomer.dev`), a reproducible bug in a dto-injection.md example (`$query['query']`/`$headers['authorization']` array access on what are actually `object` return types from `Payload::getQuery()`/`getHeaders()`), incorrect error response `message` text (`"Validation failed"` vs actual `"Request data is invalid"`), incorrect `Examples/Model/` path (actual: `Examples/Dto/`), incomplete example routes list (missing `/api-user`, `/order` and `/info`), and incorrect namespace in README.md's quick usage snippet
- A schema file loaded outside `schemas_path` (e.g. one of the bundle's own examples) with the same filename as a schema inside `schemas_path` would overwrite that directory's registration on the shared resolver, breaking any concurrently-used schema in `schemas_path` for the rest of the process's lifetime - fixed by giving each registered directory its own domain instead of reusing `schema_domain` for both
- Clarified that the bundle's `/_examples/*` routes ship with their own exception listener and don't require the manual listener setup described in quick-start.md
- Clarified that importing the bundle's `config/routes.yaml` is only needed when the host app doesn't already auto-discover attribute-routed controllers via `routing.controllers`

## [3.0.0] - 2026-03-02

### Changed
- **Restored Symfony 7.4+ support** - Bundle now supports Symfony ^7.4 and ^8.0
- Minimum PHP version lowered to 8.2 (required for Symfony 7.4)
- Updated PHPUnit compatibility to support both ^11.0 and ^12.0 versions
- Updated all Symfony dependencies to support both 7.4+ and 8.0+ versions

### Compatibility
- PHP: >=8.2
- Symfony: ^7.4 | ^8.0
- PHPUnit: ^11.0 | ^12.0

## [2.0.0] - 2026-01-27

### Breaking Changes
- **Dropped support for Symfony < 8.0** - Minimum required version is now Symfony 8.0
- Minimum PHP version raised to 8.4

### Added
- Complete headers validation support
- Priority-based validation with MapRequest attribute
- ValidatedDtoInterface for strongly typed DTOs
- VitePress documentation website with GitHub Pages deployment
- Comprehensive OpenAPI integration examples
- Enhanced error handling for empty query schemas
- Automatic GitHub Actions CI/CD workflows
- Medium-zoom integration for clickable documentation images
- Automated link validation with markdown-link-check
- Credits section in documentation acknowledging Opis JSON Schema library
- `npm run check:links` script for README.md link validation
- Post-deployment link validation in GitHub Actions workflow

### Changed
- Renamed `Request` class to `ValidatedRequest` for clarity
- Enhanced `MapRequestResolver` with headers support
- Updated `Payload` model to include headers data
- Modernized GitHub Actions workflows for Symfony 8.0+ and PHP 8.4+
- Improved OpenAPI documentation generation with nelmio/api-doc-bundle v5.9+
- Consolidated documentation structure - moved all content to `/docs/guide/` directory
- Moved `api/index.md` → `api.md` and `examples/index.md` → `examples.md` for flat structure
- Updated GitHub Actions to use Node.js 20 (required by markdown-link-check)
- Configured phpcs exclusion patterns to check only bundle files, not all vendor code

### Fixed
- Empty query schema handling in OpenAPI generation
- Headers parameter generation in API documentation
- PHPCS code style compliance with 4-space indentation
- All technical inaccuracies in documentation corrected based on actual bundle implementation
- Exception handling documentation (requires manual ExceptionListener)
- Error format examples (JSON Pointer paths with expected/received structure)
- Filter documentation to use correct `Opis\JsonSchema\Filter` interface
- Filter schema syntax to use `$filters/$func/$vars` structure
- Nested DTOs documentation (requires manual `fromPayload()` implementation)
- Removed non-existent features from docs (validationGroups parameter)
- Fixed all namespaces to `Outcomer\ValidationBundle`
- Fixed 6 dead links in documentation files
- Updated all navigation links in config.js, index.md, and guide footer sections
- Updated README.md Quick Links to match new documentation structure

### Documentation
- Complete VitePress documentation website at https://outcomer.github.io/symfony-json-schema-validation/
- Installation and quick start guides
- Advanced usage examples with headers validation
- OpenAPI integration documentation with real controller examples
- Contributing guidelines and development setup
- Schema Basics guide with JSON Schema compliance details
- Configuration reference with OPIS filters
- DTO Injection guide with ValidatedDtoInterface patterns
- Live examples section with OUTCOMER_VALIDATION_ENABLE_EXAMPLES setup
- Complete API reference documentation

### Dependencies
- PHP >= 8.4
- Symfony >= 8.0
- OPIS JSON Schema 2.0

## [1.0.0] - 2024-12-22

### Added
- Initial release of Symfony JSON Schema Validation Bundle
- JSON Schema validation for HTTP requests (body, query, path parameters)
- PHP 8.4 `MapRequest` attribute for automatic validation
- Automatic type casting for query and path parameters
- Custom OPIS filters support via ServiceLocator
- NelmioApiDocBundle integration for automatic OpenAPI documentation generation
- Schema references (`$ref`) support for reusable schemas
- Flexible error handling (throw exceptions or collect violations)
- Comprehensive README with examples and documentation
- Symfony Flex recipe for automatic configuration
- PHP_CodeSniffer configuration with Symfony coding standards
- MIT License

### Dependencies
- PHP >= 8.2
- Symfony 6.4 or 7.x
- OPIS JSON Schema 2.0
