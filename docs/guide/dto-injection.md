# DTO Injection

The bundle allows you to inject custom Data Transfer Objects (DTOs) directly into your controller methods, providing type-safe access to validated request data.

## Basic DTO Usage

Instead of working with generic `ValidatedRequest`, you can create custom DTOs that match your JSON Schema:

```php
<?php

namespace App\Dto;

use Outcomer\ValidationBundle\Model\ValidatedDtoInterface;

readonly class CreateUserDto implements ValidatedDtoInterface
{
    public function __construct(
        public string $name,
        public string $email,
        public ?int $age = null,
    ) {}
}
```

Use it in your controller:

```php
use App\Dto\CreateUserDto;
use Outcomer\ValidationBundle\Attribute\MapRequest;

#[Route('/api/users', methods: ['POST'])]
public function createUser(#[MapRequest('user-create.json')] CreateUserDto $user): JsonResponse
{
    // $user is fully typed and validated
    $this->userService->create(
        name: $user->name,
        email: $user->email,
        age: $user->age
    );
    
    return $this->json(['status' => 'created']);
}
```

## Nested DTOs

The bundle doesn't automatically create nested DTO objects - you need to implement this manually in your `fromPayload()` method. However, since the payload has already been validated against your JSON Schema, you can safely access nested data without additional checks.

```php
readonly class AddressDto
{
    public function __construct(
        public string $street,
        public string $city,
        public string $zipCode,
    ) {}
}

readonly class CreateUserDto implements ValidatedDtoInterface
{
    public function __construct(
        public string $name,
        public string $email,
        public AddressDto $address,
        public array $violations = [],
    ) {}
    
    public static function fromPayload(Payload $payload, array $violations = []): static
    {
        $data = $payload->getContent();
        
        // Payload is already validated - safe to access nested arrays
        $address = new AddressDto(
            $data['address']['street'],
            $data['address']['city'],
            $data['address']['zipCode']
        );
        
        return new static(
            $data['name'],
            $data['email'],
            $address,
            $violations
        );
    }
}
```

JSON Schema:

```json
{
  "properties": {
    "body": {
      "type": "object",
      "properties": {
        "name": {"type": "string"},
        "email": {"type": "string", "format": "email"},
        "address": {
          "type": "object",
          "properties": {
            "street": {"type": "string"},
            "city": {"type": "string"},
            "zipCode": {"type": "string"}
          },
          "required": ["street", "city", "zipCode"]
        }
      },
      "required": ["name", "email", "address"]
    }
  }
}
```

## Array Properties

Handle arrays with typed properties:

```php
readonly class CreateOrderDto implements ValidatedDtoInterface
{
    /**
     * @param string[] $items
     */
    public function __construct(
        public string $customerId,
        public array $items,
        public float $totalAmount,
        public array $violations = [],
    ) {}
    
    public static function fromPayload(Payload $payload, array $violations = []): static
    {
        $data = $payload->getContent();
        
        return new static(
            $data['customerId'],
            $data['items'],
            $data['totalAmount'],
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

JSON Schema:

```json
{
  "properties": {
    "body": {
      "type": "object",
      "properties": {
        "customerId": {"type": "string"},
        "items": {
          "type": "array",
          "items": {"type": "string"}
        },
        "totalAmount": {"type": "number"}
      }
    }
  }
}
```

## Query and Headers

Access query parameters and headers through your DTO:

```php
readonly class SearchProductsDto implements ValidatedDtoInterface
{
    public function __construct(
        public string $query,
        public int $page = 1,
        public int $limit = 20,
        public string $authorization = '',
        public array $violations = [],
    ) {}
    
    public static function fromPayload(Payload $payload, array $violations = []): static
    {
        $query = $payload->getQuery();
        $headers = $payload->getHeaders();
        
        return new static(
            $query['query'],
            $query['page'] ?? 1,
            $query['limit'] ?? 20,
            $headers['authorization'] ?? '',
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

JSON Schema:

```json
{
  "properties": {
    "query": {
      "type": "object",
      "properties": {
        "query": {"type": "string"},
        "page": {"type": "integer", "minimum": 1, "default": 1},
        "limit": {"type": "integer", "minimum": 1, "maximum": 100, "default": 20}
      },
      "required": ["query"]
    },
    "headers": {
      "type": "object",
      "properties": {
        "authorization": {"type": "string"}
      }
    }
  }
}
```

## Optional Properties

Use nullable types for optional properties:

```php
readonly class UpdateUserDto implements ValidatedDtoInterface
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?int $age = null,
        public array $violations = [],
    ) {}
    
    public static function fromPayload(Payload $payload, array $violations = []): static
    {
        $data = $payload->getContent();
        
        return new static(
            $data['name'] ?? null,
            $data['email'] ?? null,
            $data['age'] ?? null,
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

## Enums

Use PHP enums for validated string values:

```php
enum UserRole: string
{
    case ADMIN = 'admin';
    case USER = 'user';
    case GUEST = 'guest';
}

readonly class CreateUserDto implements ValidatedDtoInterface
{
    public function __construct(
        public string $name,
        public UserRole $role,
        public array $violations = [],
    ) {}
    
    public static function fromPayload(Payload $payload, array $violations = []): static
    {
        $data = $payload->getContent();
        
        return new static(
            $data['name'],
            UserRole::from($data['role']),
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

JSON Schema:

```json
{
  "properties": {
    "body": {
      "properties": {
        "name": {"type": "string"},
        "role": {
          "type": "string",
          "enum": ["admin", "user", "guest"]
        }
      }
    }
  }
}
```

## Next Steps

- **[Schema Basics →](./schema-basics)** - Understand JSON Schema structure
- **[OpenAPI Integration →](./openapi-integration)** - Auto-generate API docs
- **[Examples →](../examples/)** - See real-world DTO examples
