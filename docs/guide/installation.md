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

If using Symfony Flex (recommended) and you accept the recipe, the bundle is registered and a starter config file is created for you automatically.

The recipe lives in the community-maintained [`symfony/recipes-contrib`](https://github.com/symfony/recipes-contrib) repository, so Flex will ask before applying it (unless `extra.symfony.allow-contrib` is `true` in your `composer.json`, or you're installing non-interactively - e.g. in CI - in which case it's skipped without asking). If you decline it, don't use Flex, or aren't running interactively, register the bundle yourself:

```php
// config/bundles.php
return [
    // ...
    Outcomer\ValidationBundle\OutcomerValidationBundle::class => ['all' => true],
];
```

Then create the configuration file yourself - see [Configuration](#configuration) below.

## Configuration

::: warning This step is required
`schemas` defaults to an empty list. Without at least one entry, no schema directory is registered, and every `#[MapRequest]` call will fail with "Schema directory is not registered" - this file isn't optional customization, it's required for the bundle to work at all.
:::

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
