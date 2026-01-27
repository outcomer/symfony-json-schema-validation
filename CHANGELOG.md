# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Complete headers validation support
- Priority-based validation with MapRequest attribute
- ValidatedDtoInterface for strongly typed DTOs
- VitePress documentation website with GitHub Pages deployment
- Comprehensive OpenAPI integration examples
- Enhanced error handling for empty query schemas
- Automatic GitHub Actions CI/CD workflows

### Changed
- Renamed `Request` class to `ValidatedRequest` for clarity
- Enhanced `MapRequestResolver` with headers support
- Updated `Payload` model to include headers data
- Modernized GitHub Actions workflows for Symfony 8.0+ and PHP 8.4+
- Improved OpenAPI documentation generation with nelmio/api-doc-bundle v5.9+

### Fixed
- Empty query schema handling in OpenAPI generation
- Headers parameter generation in API documentation
- PHPCS code style compliance with 4-space indentation

### Documentation
- Complete VitePress documentation website
- Installation and quick start guides
- Advanced usage examples with headers validation
- OpenAPI integration documentation
- Contributing guidelines and development setup


### Dependencies
- PHP >= 8.4
- Symfony  8.x
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
