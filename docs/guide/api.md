# API Reference

Complete API reference for Symfony JSON Schema Validation bundle.

## Attributes

### MapRequest

The main attribute for enabling JSON Schema validation on controller method parameters.

```php
#[MapRequest(
    schemaPath: string,
    validationGroups: array = [],
    priority: int = 0
)]
```

**Parameters:**

- `schemaPath` (string, required): Path to JSON Schema file relative to `schemas_path` configuration
- `validationGroups` (array, optional): Validation groups for conditional validation
- `priority` (int, optional): Execution priority when multiple `MapRequest` attributes are used

**Example:**

```php
use Outcomer\ValidationBundle\Attribute\MapRequest;

#[Route('/api/users', methods: ['POST'])]
public function createUser(#[MapRequest('user-create.json', validationGroups: ['create'])] UserCreateDto $user): JsonResponse
{
    // ...
}
```

## Interfaces

### ValidatedDtoInterface

Marker interface for DTOs that receive validated data.

```php
namespace Outcomer\ValidationBundle\Model;

interface ValidatedDtoInterface
{
}
```

**Usage:**

```php
use Outcomer\ValidationBundle\Model\ValidatedDtoInterface;

readonly class UserCreateDto implements ValidatedDtoInterface
{
    public function __construct(
        public string $name,
        public string $email,
        public array $violations = [],
    ) {}
    
    public static function fromPayload(Payload $payload, array $violations = []): static
    {
        $data = $payload->getContent();
        
        return new static(
            $data['name'],
            $data['email'],
            $violations
        );
    }
    
    public function isValid(): bool
    {
        return empty($this->violations);
    }
    
    public function getViolations(): array
    {
        return $this->violations;
    }
}
```

### Filters

Filters use **Opis JSON Schema** interface. The bundle does not provide its own filter interface.

```php
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
        
        if (!is_string($value)) {
            return true; // Let JSON Schema handle type validation
        }
        
        // Return true if email is unique (valid)
        // Return false if email exists (invalid)
        return !$this->userRepository->emailExists($value);
    }
}
```

Register in configuration:

```yaml
outcomer_validation:
    filters:
        unique_email: App\Filter\UniqueEmailFilter
```

Use in schema:

```json
{
  "properties": {
    "body": {
      "properties": {
        "email": {
          "type": "string",
          "format": "email",
          "$filters": {
            "$func": "unique_email"
          }
        }
      }
    }
  }
}
```
```

## Models

### ValidatedRequest

Default model containing validated request data.

```php
namespace Outcomer\ValidationBundle\Model;

class ValidatedRequest
{
    public function getPayload(): ValidatedPayload;
    public function getBody(): mixed;
    public function getQuery(): mixed;
    public function getPath(): mixed;
    public function getHeaders(): mixed;
}
```

**Methods:**

- `getPayload()`: Returns full validated payload object
- `getBody()`: Returns validated request body
- `getQuery()`: Returns validated query parameters
- `getPath()`: Returns validated path parameters
- `getHeaders()`: Returns validated headers

**Example:**

```php
#[Route('/api/users', methods: ['POST'])]
public function createUser(#[MapRequest('user-create.json')] ValidatedRequest $request): JsonResponse
{
    $body = $request->getBody();
    $query = $request->getQuery();
    
    // ...
}
```

### ValidatedPayload

Container for all validated request components.

```php
namespace Outcomer\ValidationBundle\Model;

class ValidatedPayload
{
    public function getBody(): mixed;
    public function getQuery(): mixed;
    public function getPath(): mixed;
    public function getHeaders(): mixed;
}
```

## Configuration

### Bundle Configuration

Full configuration reference:

```yaml
# config/packages/outcomer_validation.yaml
outcomer_validation:
    # Directory containing JSON Schema files
    schemas_path: '%kernel.project_dir%/config/validation/schemas'
    
    # Base URL for schema references (optional)
    schema_domain: 'https://api.example.com/schemas'
    
    # Custom filters for data preprocessing
    filters:
        trim: App\Filter\TrimFilter
        lowercase: App\Filter\LowercaseFilter
```

## Exceptions

### ValidationException

Thrown when request validation fails.

```php
namespace Outcomer\ValidationBundle\Exception;

class ValidationException extends \RuntimeException
{
    public function getViolations(): array;
}
```

**Structure of violations:**

```php
[
    '/body/email' => [
        [
            'expected' => 'The data must match the \'email\' format',
            'recieved' => 'invalid@'
        ]
    ],
    '/body/age' => [
        [
            'expected' => 'Number must be greater than or equal to 21',
            'recieved' => 18
        ]
    ]
]
```

## Exception Handling

### Custom Exception Listener

The bundle **does not** automatically convert exceptions to JSON. You must create an event listener:

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

**HTTP Status:** 400 Bad Request (from `$exception->getStatusCode()`)

## Services

### SchemaValidator

Main service for validating data against JSON Schema.

```php
namespace Outcomer\ValidationBundle\Service;

class SchemaValidator
{
    public function validate(string $schemaPath, array $data): void;
}
```

**Usage (advanced):**

```php
use Outcomer\ValidationBundle\Service\SchemaValidator;

class CustomService
{
    public function __construct(
        private SchemaValidator $validator
    ) {}
    
    public function validateCustomData(array $data): void
    {
        $this->validator->validate('custom-schema.json', $data);
    }
}
```

## Supported JSON Schema Features

- **Types:** string, number, integer, boolean, array, object, null
- **Formats:** email, uri, date-time, uuid, ipv4, ipv6, hostname
- **String validation:** minLength, maxLength, pattern
- **Number validation:** minimum, maximum, multipleOf
- **Array validation:** minItems, maxItems, uniqueItems, items
- **Object validation:** properties, required, additionalProperties, minProperties, maxProperties
- **Composition:** allOf, anyOf, oneOf, not
- **Conditional:** if-then-else
- **References:** $ref
- **Enums:** enum
- **Const:** const

## Version Compatibility

| Bundle Version | PHP Version | Symfony Version | JSON Schema Draft |
|---------------|-------------|-----------------|-------------------|
| 1.x | >= 8.4 | >= 8.0 | 2020-12, 2019-09, 07 |

## Credits

This bundle is built on top of excellent open source projects:

- **[Opis JSON Schema](https://github.com/opis/json-schema)** - The powerful validation engine powering this bundle
- **[Symfony](https://symfony.com/)** - The PHP framework ecosystem
- **[JSON Schema](https://json-schema.org/)** - The specification standard

Documentation built with **[VitePress](https://vitepress.dev/)**.

## Next Steps

- **[How It Works →](./how-it-works)** - Understand the core philosophy
- **[Examples →](./examples)** - Real-world code examples
- **[GitHub →](https://github.com/outcomer/symfony-json-schema-validation)** - Source code and issues
