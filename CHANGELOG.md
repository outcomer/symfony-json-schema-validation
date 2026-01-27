# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
