# Installation

## Requirements

- PHP 8.2 or higher
- Symfony 7.4 or 8.0
- Composer

## Install via Composer

```bash
composer require outcomer/symfony-json-schema-validation
```

## Bundle Registration

If using Symfony Flex (recommended), the bundle will be automatically registered. Otherwise, add it manually:

```php
// config/bundles.php
return [
    // ...
    Outcomer\ValidationBundle\OutcomerValidationBundle::class => ['all' => true],
];
```

## Configuration

Create a configuration file to customize the bundle settings:

```yaml
# config/packages/outcomer_validation.yaml
outcomer_validation:
    schemas:
        - path: '%kernel.project_dir%/config/validation/schemas'
          domain: 'https://your-domain.com/schemas'
    filters:
        unique_email: App\Filter\UniqueEmailFilter
```

### Configuration Options

| Option | Default | Description |
|--------|---------|-------------|
| `schemas` | `[]` | Path/domain pairs for JSON schema files - see [Configuration](./configuration#schemas) |
| `filters` | `{}` | Custom data filters for preprocessing |

## Directory Structure

Create the schemas directory:

```bash
mkdir -p config/validation/schemas
```

Your project structure should look like:

```
your-project/
├── config/
│   ├── packages/
│   │   └── outcomer_validation.yaml
│   └── validation/
│       └── schemas/
│           ├── user-create.json
│           ├── user-update.json
│           └── product-search.json
├── src/
│   └── Controller/
│       └── ApiController.php
└── ...
```

## Verify Installation

Create a simple test schema and controller to verify everything works:

```json
# config/validation/schemas/test.json
{
  "type": "object",
  "properties": {
    "body": {
      "type": "object",
      "properties": {
        "message": { "type": "string" }
      },
      "required": ["message"]
    }
  }
}
```

```php
# src/Controller/TestController.php
<?php

use Outcomer\ValidationBundle\Attribute\MapRequest;
use Outcomer\ValidationBundle\Model\ValidatedRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class TestController extends AbstractController
{
    #[Route('/test', methods: ['POST'])]
    public function test(#[MapRequest('test.json')] ValidatedRequest $request): JsonResponse
    {
        return $this->json([
            'received' => $request->getPayload()->getBody()->message
        ]);
    }
}
```

Test with curl:

```bash
curl -X POST http://localhost/test \
  -H "Content-Type: application/json" \
  -d '{"message":"Hello World"}'
```

## Next Steps

- **[Quick Start →](./quick-start)** - Create your first validation
- **[Configuration →](./configuration)** - Learn about all configuration options
- **[Schema Basics →](./schema-basics)** - Understand JSON Schema fundamentals
