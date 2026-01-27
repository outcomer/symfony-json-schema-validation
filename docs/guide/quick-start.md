# Quick Start

Get up and running with JSON Schema validation in 5 minutes!

## Step 1: Create Your First Schema

Create a JSON schema file for user registration:

```json
# config/validation/schemas/user-register.json
{
  "$schema": "http://json-schema.org/draft-07/schema#",
  "type": "object",
  "properties": {
    "body": {
      "type": "object",
      "properties": {
        "name": {
          "type": "string",
          "minLength": 2,
          "maxLength": 50,
          "description": "User's full name",
          "example": "John Doe"
        },
        "email": {
          "type": "string",
          "format": "email",
          "description": "User's email address",
          "example": "john@example.com"
        },
        "age": {
          "type": "integer",
          "minimum": 18,
          "maximum": 120,
          "description": "User's age",
          "example": 25
        }
      },
      "required": ["name", "email"],
      "additionalProperties": false
    },
    "headers": {
      "type": "object",
      "properties": {
        "authorization": {
          "type": "string",
          "pattern": "^Bearer .+",
          "description": "JWT token",
          "example": "Bearer eyJhbGciOiJIUzI1NiI..."
        }
      },
      "required": ["authorization"]
    }
  }
}
```

## Step 2: Create a Controller

Use the `#[MapRequest]` attribute in your controller:

```php
<?php

namespace App\Controller;

use Outcomer\ValidationBundle\Attribute\MapRequest;
use Outcomer\ValidationBundle\Model\ValidatedRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/api/users/register', name: 'user_register', methods: ['POST'])]
    public function register(#[MapRequest('user-register.json')] ValidatedRequest $request): JsonResponse
    {
        $payload = $request->getPayload();
        $userData = $payload->getBody();
        $headers = $payload->getHeaders();
        
        // Data is guaranteed to be valid at this point!
        
        return $this->json([
            'success' => true,
            'message' => "User {$userData->name} registered successfully",
            'data' => [
                'name' => $userData->name,
                'email' => $userData->email,
                'age' => $userData->age ?? null,
            ]
        ], 201);
    }
}
```

## Step 3: Test Your Endpoint

### Valid Request
```bash
curl -X POST http://localhost/api/users/register \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer your-jwt-token" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "age": 25
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "User John Doe registered successfully",
  "data": {
    "name": "John Doe",
    "email": "john@example.com",
    "age": 25
  }
}
```

### Invalid Request
```bash
curl -X POST http://localhost/api/users/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "A",
    "email": "invalid-email"
  }'
```

**Response (400 Bad Request):**
```json
{
  "message": "Validation failed",
  "errors": {
    "/body/name": [
      {
        "expected": "String should have a minimum length of 2",
        "recieved": "A"
      }
    ],
    "/body/email": [
      {
        "expected": "The data must match the 'email' format",
        "recieved": "invalid-email"
      }
    ],
    "/headers/authorization": [
      {
        "expected": "The property authorization is required",
        "recieved": null
      }
    ]
  }
}
```

::: warning Exception Handling Required
The formatted JSON error response above requires setting up an exception listener. By default, Symfony will show a generic error page.

**Setup Exception Listener:**

1. Create the handler:

```php
// src/Exception/Handler/ValidationExceptionHandler.php
namespace App\Exception\Handler;

use Outcomer\ValidationBundle\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ValidationExceptionHandler
{
    public function handle(ValidationException $exception, ExceptionEvent $event): void
    {
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
```

2. Create the event listener:

```php
// src/EventListener/ExceptionListener.php
namespace App\EventListener;

use App\Exception\Handler\ValidationExceptionHandler;
use Outcomer\ValidationBundle\Exception\ValidationException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::EXCEPTION, method: 'handleException', priority: 0)]
class ExceptionListener
{
    public function __construct(
        private readonly ValidationExceptionHandler $handler
    ) {}

    public function handleException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        
        if ($exception instanceof ValidationException) {
            $this->handler->handle($exception, $event);
        }
    }
}
```

That's it! Symfony will autowire everything automatically.
:::

## Step 4: Advanced Usage with DTOs

Create a custom DTO for type-safe data access:

```php
<?php

namespace App\Dto;

use Outcomer\ValidationBundle\Model\Payload;
use Outcomer\ValidationBundle\Model\ValidatedDtoInterface;

final readonly class UserRegistrationDto implements ValidatedDtoInterface
{
    public function __construct(
        public string $name,
        public string $email,
        public ?int $age = null,
        public string $authToken = '',
        public array $validationErrors = []
    ) {}

    public static function fromPayload(Payload $payload, array $violations = []): self
    {
        $body = $payload->getBody();
        $headers = $payload->getHeaders();
        
        return new self(
            name: $body->name ?? '',
            email: $body->email ?? '',
            age: $body->age ?? null,
            authToken: $headers->authorization ?? '',
            validationErrors: $violations
        );
    }

    public function isValid(): bool
    {
        return empty($this->validationErrors);
    }

    public function getViolations(): array
    {
        return $this->validationErrors;
    }
}
```

Update your controller to use the DTO:

```php
#[Route('/api/users/register', name: 'user_register', methods: ['POST'])]
public function register(#[MapRequest('user-register.json')] UserRegistrationDto $userDto): JsonResponse {
    if (!$userDto->isValid()) {
        return $this->json([
            'message' => 'Validation failed',
            'errors' => $userDto->getViolations()
        ], 400);
    }
    
    // Use typed DTO properties
    return $this->json([
        'message' => "Welcome, {$userDto->name}!",
        'email' => $userDto->email,
        'age' => $userDto->age
    ]);
}
```

## What's Next?

You now have a working validation setup! Explore more advanced features:

- **[Schema Basics →](./schema-basics)** - Learn JSON Schema in detail
- **[DTO Injection →](./dto-injection)** - Advanced DTO patterns
- **[OpenAPI Integration →](./openapi-integration)** - Automatic API documentation
- **[Examples →](./examples)** - More real-world examples

## Common Patterns

### Optional Fields
```json
{
  "properties": {
    "age": { 
      "type": "integer",
      "description": "Optional age field"
    }
  }
  // Note: age is not in "required" array
}
```

### Conditional Validation
```json
{
  "if": {
    "properties": { "type": { "const": "premium" } }
  },
  "then": {
    "required": ["creditCard"]
  }
}
```

### Custom Formats
```json
{
  "phoneNumber": {
    "type": "string",
    "pattern": "^\\+[1-9]\\d{1,14}$"
  }
}
```
