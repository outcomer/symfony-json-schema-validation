---
layout: home

hero:
  name: "Symfony JSON Schema Validation"
  text: "Powerful request validation for modern Symfony applications"
  tagline: "Type-safe JSON Schema validation with automatic OpenAPI documentation generation"
  image:
    src: /logo-large.svg
    alt: Symfony JSON Schema Validation
  actions:
    - theme: brand
      text: How It Works
      link: /guide/how-it-works
    - theme: alt
      text: Get Started
      link: /guide/quick-start
    - theme: alt
      text: View Examples
      link: /guide/examples

features:
  - icon: 🛡️
    title: Type-Safe Validation
    details: Strong typing with ValidatedDtoInterface ensures your data is always valid and properly structured.
  
  - icon: 📋
    title: JSON Schema Integration
    details: Use industry-standard JSON Schema for request validation with automatic type conversion and error handling.
  
  - icon: 📖
    title: OpenAPI Documentation
    details: Automatic OpenAPI/Swagger documentation generation from your JSON schemas with interactive examples.
  
  - icon: 🎯
    title: Modern Symfony
    details: Built for Symfony 8.0+ and PHP 8.4+ with full support for attributes and modern PHP features.
  
  - icon: ⚡
    title: High Performance
    details: Optimized validation with OPIS JSON Schema library and intelligent caching mechanisms.
  
  - icon: 🧩
    title: Extensible
    details: Custom DTO injection, flexible error handling, and seamless integration with existing Symfony workflows.
---

## Quick Start

Install the bundle via Composer:

```bash
composer require outcomer/symfony-json-schema-validation
```

Create a JSON schema:

```json
{
  "type": "object",
  "properties": {
    "body": {
      "type": "object",
      "properties": {
        "name": { "type": "string", "minLength": 3 },
        "email": { "type": "string", "format": "email" }
      },
      "required": ["name", "email"]
    }
  }
}
```

Use it in your controller:

```php
#[Route('/api/users', methods: ['POST'])]
public function createUser(#[MapRequest('user-create.json')] ValidatedRequest $request): JsonResponse
{
    $data = $request->getPayload()->getBody();
    // $data is guaranteed to be valid!
    
    return $this->json(['message' => 'User created']);
}
```

## What's Next?

- **[How It Works →](/guide/how-it-works)** - Understand the Single Source of Truth philosophy
- **[Installation →](/guide/installation)** - Step-by-step installation guide  
- **[Examples →](/guide/examples)** - Real-world usage examples
- **[API Reference →](/guide/api)** - Complete API documentation
