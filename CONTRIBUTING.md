# Contributing to Symfony JSON Schema Validation Bundle

Thank you for considering contributing to this project! We welcome contributions from everyone.

## 📋 Development Setup

### Prerequisites

- PHP 8.4+
- Composer
- Git
- Node.js 18+ (for documentation)

### Local Development

```bash
# Clone the repository
git clone https://github.com/outcomer/symfony-json-schema-validation.git
cd symfony-json-schema-validation

# Install dependencies
composer install

# Run tests
./bin/phpunit

# Run code style checks
./vendor/bin/phpcs

# Fix code style automatically
./vendor/bin/phpcbf
```

### Documentation Development

```bash
# Install documentation dependencies
npm install

# Start development server
npm run docs:dev

# Build static documentation
npm run docs:build
```

## 🔧 Development Guidelines

### Code Style

- Follow PSR-12 coding standards
- Use 4 spaces for indentation (not tabs)
- Keep line length under 120 characters
- Use type hints and return types
- Add DocBlocks for public methods and classes

### Testing

- Write unit tests for new functionality
- Ensure all tests pass before submitting PR
- Aim for high test coverage
- Use meaningful test names that describe what is being tested

### Commit Guidelines

Follow [Conventional Commits](https://www.conventionalcommits.org/) format:

```
type(scope): description

[optional body]

[optional footer]
```

**Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `ci`: CI/CD changes

**Examples:**
```
feat(validation): add headers validation support
fix(resolver): handle empty query schemas correctly
docs(readme): update installation instructions
```

## 🚀 Contributing Process

### 1. Fork and Clone

```bash
# Fork the repository on GitHub
# Then clone your fork
git clone https://github.com/YOUR_USERNAME/symfony-json-schema-validation.git
```

### 2. Create Feature Branch

```bash
git checkout -b feature/your-feature-name
```

### 3. Make Changes

- Write your code
- Add/update tests
- Update documentation if needed
- Run tests and code style checks

### 4. Submit Pull Request

1. Push your changes to your fork
2. Create a Pull Request against the main branch
3. Fill out the PR template
4. Wait for review

### Pull Request Requirements

- [ ] All tests pass
- [ ] Code follows project style guidelines
- [ ] New features have tests
- [ ] Documentation is updated (if applicable)
- [ ] Commit messages follow conventional format
- [ ] PR description explains the changes

## 🐛 Reporting Issues

### Bug Reports

When reporting bugs, please include:

1. **Clear description** of the issue
2. **Steps to reproduce** the problem
3. **Expected behavior** vs actual behavior
4. **Environment details** (PHP version, Symfony version, etc.)
5. **Code samples** or screenshots if relevant

### Feature Requests

For new features, please:

1. **Check existing issues** to avoid duplicates
2. **Explain the use case** and why it's needed
3. **Provide examples** of how it would work
4. **Consider backwards compatibility**

## 📚 Documentation Contributions

Documentation improvements are always welcome:

- Fix typos or unclear explanations
- Add examples and use cases
- Improve API documentation
- Translate documentation (future)

### Documentation Structure

```
docs/
├── .vitepress/          # VitePress configuration
├── index.md            # Homepage
├── guide/              # User guides
│   ├── installation.md
│   ├── quick-start.md
│   ├── configuration.md
│   ├── advanced.md
│   └── openapi.md
└── api/                # API reference
    └── index.md
```

## 🔄 Release Process

Releases are automated via GitHub Actions:

1. Update version in `composer.json`
2. Update CHANGELOG.md
3. Create a new tag
4. Push to main branch
5. GitHub Actions will create the release

## 💬 Communication

- **GitHub Issues**: Bug reports and feature requests
- **GitHub Discussions**: Questions and general discussion
- **Pull Requests**: Code contributions

## 📜 Code of Conduct

We're committed to providing a welcoming and inclusive environment for all contributors. Please be respectful and professional in all interactions.

## 🙏 Recognition

Contributors will be:
- Listed in CONTRIBUTORS.md
- Mentioned in release notes for significant contributions
- Given appropriate credit in documentation

Thank you for contributing to making this project better! 🎉
