# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2024-12-22

### Added
- Initial release of Outcomer Validation Bundle
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
