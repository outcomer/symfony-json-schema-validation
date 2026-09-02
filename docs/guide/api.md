# API Reference

Complete API reference for Symfony JSON Schema Validation bundle.

## Attributes

### MapRequest

The main attribute for enabling JSON Schema validation on controller method parameters.

```php
#[MapRequest(
    schema: string,
    resolver: string = MapRequestResolver::class,
    triggerResponse: bool = true
)]
```

**Parameters:**

- `schema` (string, required): Path to JSON Schema file relative to `schemas_path` configuration
- `resolver` (string, optional): Custom resolver class for advanced usage
- `triggerResponse` (bool, optional): Trigger immediate 400 response on validation failure (`true`) or collect violations (`false`)

**Example:**

```php
use Outcomer\ValidationBundle\Attribute\MapRequest;

#[Route('/api/users', methods: ['POST'])]
public function createUser(#[MapRequest('user-create.json')] UserCreateDto $user): JsonResponse
{
    // ...
}
```

## Interfaces

### ValidatedDtoInterface

Interface for DTOs that receive validated data. Implementations are built via `fromPayload()`, and expose whether validation succeeded.

```php
namespace Outcomer\ValidationBundle\Model;

interface ValidatedDtoInterface
{
    public static function fromPayload(Payload $payload, array $violations = []): static;

    public function isValid(): bool;

    public function getViolations(): array;
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
        $data = $payload->getBody();
        
        return new static(
            $data->name,
            $data->email,
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

## Models

### ValidatedRequest

Default DTO containing the validated payload and validation results. Used as the controller argument type when you don't need a custom DTO.

```php
namespace Outcomer\ValidationBundle\Model;

class ValidatedRequest implements ValidatedDtoInterface
{
    public function getPayload(): Payload;
    public function getViolations(): array;
    public function hasViolations(): bool;
    public function isValid(): bool;
    public function getStatus(): ValidationStatus;
}
```

**Methods:**

- `getPayload()`: Returns the validated `Payload` (body, query, path, headers)
- `getViolations()`: Returns validation violations, if any
- `hasViolations()` / `isValid()`: Whether validation failed/succeeded
- `getStatus()`: Returns a `ValidationStatus` enum (`VALID` or `INVALID`)

**Example:**

```php
#[Route('/api/users', methods: ['POST'])]
public function createUser(#[MapRequest('user-create.json')] ValidatedRequest $request): JsonResponse
{
    $body = $request->getPayload()->getBody();
    $query = $request->getPayload()->getQuery();
    
    // ...
}
```

### Payload

Container for all validated request components.

```php
namespace Outcomer\ValidationBundle\Model;

final class Payload
{
    public function getBody(): object|array|null;
    public function getQuery(): object;
    public function getPath(): object;
    public function getHeaders(): object;
}
```

`getQuery()`, `getPath()` and `getHeaders()` always return an object (property access, e.g. `$payload->getQuery()->page`). `getBody()` can be an object, array, or null depending on the schema and request body.

## Configuration

### Bundle Configuration

Full configuration reference:

```yaml
# config/packages/outcomer_validation.yaml
outcomer_validation:
    # Path/domain pairs for your JSON Schema files (schemas_path/schema_domain are deprecated since 4.0)
    schemas:
        - path: '%kernel.project_dir%/config/validation/schemas'
          domain: 'https://api.example.com/schemas'

    # Automatically cast numeric/boolean strings before validation
    auto_cast_query: true
    auto_cast_path: true

    # Custom filters for dynamic (non-static) validation rules
    filters:
        unique_email: App\Filter\UniqueEmailFilter
```

## Exceptions

### ValidationException

Domain exception thrown internally when request validation fails. Not tied to HTTP - see `HttpValidationException` below for the HTTP-transport wrapper.

```php
namespace Outcomer\ValidationBundle\Exception;

class ValidationException extends \RuntimeException
{
    public function getValidationErrors(): array;
}
```

### HttpValidationException

HTTP-transport wrapper thrown by `MapRequestResolver` (when `#[MapRequest]`'s `triggerResponse` is `true`, the default). Wraps the domain `ValidationException`.

```php
namespace Outcomer\ValidationBundle\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

final class HttpValidationException extends HttpException
{
    public function getValidationErrors(): array;
}
```

**Structure of validation errors:**

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

The bundle **does not** automatically convert exceptions to JSON. You must create an event listener for `HttpValidationException`:

```php
// src/EventListener/ExceptionListener.php
namespace App\EventListener;

use Outcomer\ValidationBundle\Exception\HttpValidationException;
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
        
        if ($exception instanceof HttpValidationException) {
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
  "message": "Request data is invalid",
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
namespace Outcomer\ValidationBundle\Schema;

class SchemaValidator
{
    public function validate(mixed $data, array $schema): void;
    public function validateFileSchema(mixed $data, string $schemaPath): void;
}
```

**Usage (advanced):**

```php
use Outcomer\ValidationBundle\Schema\SchemaValidator;

class CustomService
{
    public function __construct(
        private SchemaValidator $validator
    ) {}
    
    public function validateCustomData(array $data, string $schemaPath): void
    {
        $this->validator->validateFileSchema($data, $schemaPath);
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
| 4.x | >= 8.2 | ^7.4 \| ^8.0 | 2020-12, 2019-09, 07 |

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
