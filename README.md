# Stop writing DTOs, validation and OpenAPI three times in Symfony

[![GitHub Actions](https://github.com/outcomer/symfony-json-schema-validation/workflows/CI/badge.svg)](https://github.com/outcomer/symfony-json-schema-validation/actions)
[![Latest Stable Version](https://img.shields.io/packagist/v/outcomer/symfony-json-schema-validation?label=stable)](https://packagist.org/packages/outcomer/symfony-json-schema-validation)
[![PHP Version](https://img.shields.io/badge/php->=8.2-blue.svg)](https://php.net/)
[![Symfony Version](https://img.shields.io/badge/symfony-7.4+%20%7C%208.0+-green.svg)](https://symfony.com/)
[![License](https://img.shields.io/packagist/l/outcomer/symfony-json-schema-validation)](https://packagist.org/packages/outcomer/symfony-json-schema-validation)

## Before

Typical Symfony API endpoint:

- Request DTO
- Symfony Validator constraints
- OpenAPI annotations
- Mapping logic

Same field described multiple times.

```php
class CreateUserRequest
{
    #[Assert\NotBlank]
    #[Assert\Email]
    public string $email;
}
```

Plus OpenAPI annotations and mapping.

This logic is repeated in 3 different places in real projects.

---

## After

One schema:

```json
{
  "type": "object",
  "required": ["email"],
  "properties": {
    "email": {
      "type": "string",
      "format": "email"
    }
  }
}
```

Used for:
- validation
- request mapping
- OpenAPI generation

One schema replaces all of this duplication.

---

## ⚡ Quick Start

### Installation

```bash
composer require outcomer/symfony-json-schema-validation
```

### Basic Usage

```php
use Outcomer\ValidationBundle\Attribute\MapRequest;

class UserController
{
    #[Route('/api/users', methods: ['POST'])]
    public function create(
        #[MapRequest('schemas/user-create.json')]
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

---

## 📚 Why
Read the story behind this bundle on [Hashnode](https://outcomer.hashnode.dev/symfony-bundle-that-validates-anything-and-everything)

## 📖 Documentation

**[Complete Documentation](https://outcomer.github.io/symfony-json-schema-validation/)** - Visit our comprehensive documentation website

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

## 🚀 Features

- Single source of truth for API validation
- No serializer groups
- Automatic OpenAPI generation

## When NOT to use this

Use API Platform if you need:
- full CRUD automation
- admin panels
- heavy framework magic

## Incremental adoption

You can use this bundle:

- on a single endpoint
- together with Symfony Validator
- together with API Platform
- without rewriting your application

No migration is required.

## FAQ

### Does this replace Symfony Validator?

No. You can use both together.

### Does this work with API Platform?

Yes. The bundle can coexist with API Platform.

### Is this all-or-nothing?

No. You can adopt it endpoint-by-endpoint.

### Why use JSON Schema?

To avoid duplication between validation, request mapping and OpenAPI.

### Is there vendor lock-in?

No. Your schemas remain standard JSON Schema documents.

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

**Need Help?** 
- 📖 Check our [documentation](https://outcomer.github.io/symfony-json-schema-validation/)
- 🐛 [Report issues](https://github.com/outcomer/symfony-json-schema-validation/issues)
- 💬 [Join discussions](https://github.com/outcomer/symfony-json-schema-validation/discussions)
