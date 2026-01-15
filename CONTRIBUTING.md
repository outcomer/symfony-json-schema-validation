# Contributing to Outcomer Validation Bundle

Thank you for considering contributing to the Outcomer Validation Bundle! This document provides guidelines and instructions for contributing.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Making Changes](#making-changes)
- [Testing](#testing)
- [Coding Standards](#coding-standards)
- [Submitting Changes](#submitting-changes)

## Code of Conduct

This project adheres to a code of conduct. By participating, you are expected to uphold this code. Please be respectful and constructive in all interactions.

## Getting Started

1. **Fork the repository** on GitHub
2. **Clone your fork** locally:
   ```bash
   git clone https://github.com/YOUR-USERNAME/symfony-json-schema-validation.git
   cd symfony-json-schema-validation
   ```
3. **Add upstream remote**:
   ```bash
   git remote add upstream https://github.com/outcomer/symfony-json-schema-validation.git
   ```

## Development Setup

### Prerequisites

- PHP 8.2 or higher
- Composer 2.x
- Git

### Install Dependencies

```bash
composer install
```

### Running Examples

The bundle includes working examples. To enable them:

1. Set environment variable:
   ```bash
   OUTCOMER_VALIDATION_ENABLE_EXAMPLES=true
   ```

2. The examples are located in `src/Examples/`

## Making Changes

### Creating a Branch

Create a new branch for your feature or bugfix:

```bash
git checkout -b feature/my-new-feature
# or
git checkout -b bugfix/issue-123
```

### Branch Naming Convention

- `feature/` - New features
- `bugfix/` - Bug fixes
- `docs/` - Documentation changes
- `refactor/` - Code refactoring
- `test/` - Adding or updating tests

## Testing

### Running Tests

Run the full test suite:

```bash
composer test
```

### Running Tests with Coverage

Generate code coverage report:

```bash
composer test-coverage
```

Coverage report will be generated in `build/coverage/index.html`

### Writing Tests

- Place tests in the `tests/` directory
- Follow the same namespace structure as `src/`
- Test class names should end with `Test`
- Use descriptive test method names with `test` prefix

Example:

```php
<?php
namespace Outcomer\ValidationBundle\Tests\Helpers;

use PHPUnit\Framework\TestCase;

final class ArraysTest extends TestCase
{
    public function testGetNestedValueReturnsCorrectValue(): void
    {
        // Test implementation
    }
}
```

## Coding Standards

This project follows [Symfony Coding Standards](https://symfony.com/doc/current/contributing/code/standards.html).

### Check Coding Standards

```bash
composer cs-check
```

### Fix Coding Standards

```bash
composer cs-fix
```

### Key Standards

- Use PHP 8.2+ features (typed properties, constructor property promotion, etc.)
- Use `declare(strict_types=1);` in all PHP files
- Use meaningful variable and method names
- Add PHPDoc blocks for classes and methods
- Keep methods focused and small
- Follow PSR-12 coding style

## Submitting Changes

### Before Submitting

1. **Ensure tests pass**:
   ```bash
   composer test
   ```

2. **Check coding standards**:
   ```bash
   composer cs-check
   ```

3. **Update documentation** if needed

4. **Update CHANGELOG.md** following [Keep a Changelog](https://keepachangelog.com/) format

### Creating a Pull Request

1. **Push your changes** to your fork:
   ```bash
   git push origin feature/my-new-feature
   ```

2. **Create a Pull Request** on GitHub

3. **Fill in the PR template** with:
   - Clear description of changes
   - Reference to related issues
   - Screenshots (if UI changes)
   - Checklist of completed tasks

### Pull Request Guidelines

- **One feature per PR** - Keep changes focused
- **Write clear commit messages** - Use conventional commits format:
  - `feat:` - New feature
  - `fix:` - Bug fix
  - `docs:` - Documentation changes
  - `test:` - Adding tests
  - `refactor:` - Code refactoring

- **Keep commits atomic** - Each commit should be a logical unit

Example commit messages:
```
feat: add support for custom error messages
fix: resolve issue with nested schema references
docs: update installation instructions
test: add tests for MapRequest attribute
```

### Review Process

- Maintainers will review your PR
- Address any requested changes
- Once approved, your PR will be merged

## Questions?

If you have questions:

- Open an issue on GitHub
- Email: 773021792e@gmail.com

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

Thank you for contributing! 🎉
