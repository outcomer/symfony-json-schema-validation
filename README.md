# Symfony JSON Schema Validation Bundle

A powerful and flexible JSON Schema validation solution for Symfony applications with automatic OpenAPI documentation generation.

[![GitHub Actions](https://github.com/outcomer/symfony-json-schema-validation/workflows/CI/badge.svg)](https://github.com/outcomer/symfony-json-schema-validation/actions)
[![Latest Stable Version](https://poser.pugx.org/outcomer/symfony-json-schema-validation/v/stable.svg?v=2)](https://packagist.org/packages/outcomer/symfony-json-schema-validation)
[![PHP Version](https://img.shields.io/badge/php->=8.4-blue.svg)](https://php.net/)
[![Symfony Version](https://img.shields.io/badge/symfony-8.0+-green.svg)](https://symfony.com/)
[![License](https://poser.pugx.org/outcomer/symfony-json-schema-validation/license.svg)](https://packagist.org/packages/outcomer/symfony-json-schema-validation)

## 🚀 Features

- **Complete Request Validation**: Validate request body, query parameters, path variables, and headers
- **Automatic OpenAPI Documentation**: Generate API documentation with nelmio/api-doc-bundle integration
- **Priority-Based Validation**: Control validation order with MapRequest priority system
- **Type-Safe Results**: Strongly typed validated data with ValidatedDtoInterface support
- **Comprehensive Error Handling**: Detailed validation errors with JSON Schema feedback
- **Modern Symfony Integration**: Full support for Symfony 8.0+ with attribute-based configuration

## 📖 Documentation

📚 **[Complete Documentation](https://outcomer.github.io/symfony-json-schema-validation/)** - Visit our comprehensive documentation website

### Quick Links

- [🔗 How It Works](https://outcomer.github.io/symfony-json-schema-validation/guide/how-it-works)
- [🔗 Installation Guide](https://outcomer.github.io/symfony-json-schema-validation/guide/installation)
- [🔗 Quick Start Tutorial](https://outcomer.github.io/symfony-json-schema-validation/guide/quick-start)
- [🔗 Schema Basics](https://outcomer.github.io/symfony-json-schema-validation/guide/schema-basics)
- [🔗 Configuration Options](https://outcomer.github.io/symfony-json-schema-validation/guide/configuration)
- [🔗 DTO Injection](https://outcomer.github.io/symfony-json-schema-validation/guide/dto-injection)
- [🔗 OpenAPI Integration](https://outcomer.github.io/symfony-json-schema-validation/guide/openapi-integration)
- [🔗 Examples](https://outcomer.github.io/symfony-json-schema-validation/guide/examples)
- [🔗 API Reference](https://outcomer.github.io/symfony-json-schema-validation/guide/api)

## ⚡ Quick Start

### Installation

```bash
composer require outcomer/symfony-json-schema-validation
```

### Basic Usage

```php
use Outcomer\Bundle\SymfonyJsonSchemaValidation\Attribute\MapRequest;

class UserController
{
    #[Route('/api/users', methods: ['POST'])]
    public function create(
        #[MapRequest(
            schemaPath: 'schemas/user-create.json',
            validationGroups: ['create']
        )]
        UserCreateDto $user
    ): JsonResponse {
        // $user contains validated data from request body, query, path, and headers
        return new JsonResponse(['id' => $userService->create($user)]);
    }
}
```

### JSON Schema Example

```json
{
  "$schema": "https://json-schema.org/draft/2020-12/schema",
  "type": "object",
  "properties": {
    "body": {
      "type": "object", 
      "properties": {
        "name": {"type": "string", "minLength": 1},
        "email": {"type": "string", "format": "email"}
      },
      "required": ["name", "email"]
    },
    "query": {
      "type": "object",
      "properties": {
        "locale": {"type": "string", "enum": ["en", "de", "fr"]}
      }
    },
    "headers": {
      "type": "object",
      "properties": {
        "x-api-version": {"type": "string", "pattern": "^v[1-9]$"}
      }
    }
  }
}
```

## 🎯 Key Benefits

- **Developer Experience**: Intuitive attribute-based validation with full IDE support
- **API Documentation**: Automatic OpenAPI spec generation with zero configuration
- **Production Ready**: Battle-tested with comprehensive error handling and logging
- **Flexible Schema**: Support for complex validation scenarios across all request components
- **Modern PHP**: Takes advantage of PHP 8.4+ features and Symfony 8.0+ improvements

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

**Need Help?** 
- 📖 Check our [documentation](https://outcomer.github.io/symfony-json-schema-validation/)
- 🐛 [Report issues](https://github.com/outcomer/symfony-json-schema-validation/issues)
- 💬 [Join discussions](https://github.com/outcomer/symfony-json-schema-validation/discussions)
