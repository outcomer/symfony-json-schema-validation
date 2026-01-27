# Configuration

This page covers all configuration options available in the Symfony JSON Schema Validation bundle.

## Basic Configuration

Create or update your bundle configuration file:

```yaml
# config/packages/outcomer_validation.yaml
outcomer_validation:
    schemas_path: '%kernel.project_dir%/config/validation/schemas'
    schema_domain: 'https://your-domain.com/schemas'
    filters:
        unique_email: App\Filter\UniqueEmailFilter
        valid_promo_code: App\Filter\PromoCodeFilter
```

## Configuration Reference

### schemas_path

**Type:** `string`  
**Default:** `%kernel.project_dir%/config/validation/schemas`

Directory where your JSON Schema files are stored.

```yaml
outcomer_validation:
    schemas_path: '%kernel.project_dir%/schemas'
```

### schema_domain

**Type:** `string|null`  
**Default:** `null`

Base URL for schema references. Useful when you have external schema references.

```yaml
outcomer_validation:
    schema_domain: 'https://api.example.com/schemas'
```

### filters

**Type:** `array`  
**Default:** `{}`

Custom validation filters for dynamic validation logic. **Important:** Filters do not modify data - they return `true` (valid) or `false` (invalid).

```yaml
outcomer_validation:
    filters:
        unique_email: App\Filter\UniqueEmailFilter
        valid_promo_code: App\Filter\PromoCodeFilter
        user_exists: App\Filter\UserExistsFilter
```

## Custom Filters

Filters enable **dynamic validation** that goes beyond JSON Schema's static rules. Use them for:
- Database uniqueness checks
- External API validations
- Business logic constraints

**Key concept:** Filters **validate**, not transform. They return boolean results.

**Important:** Filters must implement `Opis\JsonSchema\Filter` interface from Opis JSON Schema library.

### Example: Email Uniqueness Check

```php
<?php

namespace App\Filter;

use App\Repository\UserRepository;
use Opis\JsonSchema\Filter;
use Opis\JsonSchema\ValidationContext;
use Opis\JsonSchema\Schema;

class UniqueEmailFilter implements Filter
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {}

    public function validate(ValidationContext $context, Schema $schema, array $args = []): bool
    {
        $value = $context->currentData();
        
        // Return true if valid (email is unique)
        // Return false if invalid (email already exists)
        
        if (!is_string($value)) {
            return true; // Not our concern, let JSON Schema handle type validation
        }
        
        return !$this->userRepository->emailExists($value);
    }
}
```

### Example: Promo Code Validation with Parameters

```php
<?php

namespace App\Filter;

use App\Service\PromoCodeService;
use Opis\JsonSchema\Filter;
use Opis\JsonSchema\ValidationContext;
use Opis\JsonSchema\Schema;

class PromoCodeFilter implements Filter
{
    public function __construct(
        private readonly PromoCodeService $promoService
    ) {}

    public function validate(ValidationContext $context, Schema $schema, array $args = []): bool
    {
        $value = $context->currentData();
        
        if (!is_string($value)) {
            return true;
        }
        
        // Access parameters from $vars
        $requiredLength = $args['minLength'] ?? 6;
        $prefix = $args['prefix'] ?? null;
        
        return $this->promoService->isValid($value, $requiredLength, $prefix);
    }
}
```

**Schema with filter parameters:**

```json
{
  "properties": {
    "body": {
      "properties": {
        "promoCode": {
          "type": "string",
          "$filters": {
            "$func": "valid_promo_code",
            "$vars": {
              "minLength": 8,
              "prefix": "SUMMER"
            }
          }
        }
      }
    }
  }
}
```

### Registering Filters

**Step 1:** Register filter in configuration with a unique name:

```yaml
# config/packages/outcomer_validation.yaml
outcomer_validation:
    filters:
        unique_email: App\Filter\UniqueEmailFilter      # Key = filter name
        valid_promo_code: App\Filter\PromoCodeFilter
```

**Step 2:** Use the same name in `$func` in your schema:

```json
{
  "properties": {
    "body": {
      "properties": {
        "email": {
          "type": "string",
          "format": "email",
          "$filters": {
            "$func": "unique_email"  // Must match key in configuration
          }
        },
        "promoCode": {
          "type": "string",
          "$filters": {
            "$func": "valid_promo_code",  // Must match key in configuration
            "$vars": {
              "minLength": 8,
              "prefix": "SUMMER"
            }
          }
        }
      }
    }
  }
}
```

::: tip Filter Name Matching
The value in `"$func": "unique_email"` **must exactly match** the key in your `outcomer_validation.filters` configuration.

Configuration key: `unique_email:` → Schema: `"$func": "unique_email"`
:::

**Validation flow:**
1. JSON Schema validates type and format
2. If valid, bundle looks up filter by name from `$func`
3. Filter executes with parameters from `$vars` (if any)
4. If filter returns `false`, validation fails

## Environment-Specific Configuration

You can have different configurations for different environments:

```yaml
# config/packages/dev/outcomer_validation.yaml
outcomer_validation:
    schemas_path: '%kernel.project_dir%/dev-schemas'
```

```yaml
# config/packages/prod/outcomer_validation.yaml
outcomer_validation:
    schema_domain: 'https://production-api.com/schemas'
```

## Error Handling

The bundle throws `ValidationException` when validation fails. To format it as JSON response, you need to set up an exception listener:

```php
// src/EventListener/ExceptionListener.php
namespace App\EventListener;

use Outcomer\ValidationBundle\Exception\ValidationException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::EXCEPTION, method: 'handleException', priority: 0)]
class ExceptionListener
{
    public function handleException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        
        if ($exception instanceof ValidationException) {
            $response = new JsonResponse(
                data: [
                    'message' => $exception->getMessage(),
                    'errors'  => $exception->getValidationErrors(),
                ],
                status: $exception->getStatusCode()
            );
            
            $event->setResponse($response);
        }
    }
}
```

**Response format:**

```json
{
  "message": "Validation failed",
  "errors": {
    "/body/email": [
      {
        "expected": "The data must match the 'email' format",
        "recieved": "invalid@"
      }
    ],
    "/body/name": [
      {
        "expected": "String should have a minimum length of 2",
        "recieved": "A"
      }
    ]
  }
}
```

## Next Steps

- **[Schema Basics →](./schema-basics)** - Learn JSON Schema fundamentals
- **[DTO Injection →](./dto-injection)** - Use custom DTOs
