# Examples

Real-world examples of using Symfony JSON Schema Validation in various scenarios.

## Try Live Examples

The bundle includes working example controllers that you can test in your own project. To enable them:

1. Set environment variable in your `.env.local`:
   ```bash
   OUTCOMER_VALIDATION_ENABLE_EXAMPLES=true
   ```

2. Import example routes in `config/routes.php`:
   ```php
   <?php
   
   use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
   
   return function (RoutingConfigurator $routes): void {
       // Your existing routes...
       
       // Enable example routes conditionally
       if (($_ENV['OUTCOMER_VALIDATION_ENABLE_EXAMPLES'] ?? 'false') === 'true') {
           $routes->import('../vendor/outcomer/symfony-json-schema-validation/config/routes.yaml');
       }
   };
   ```

3. Clear cache and access example routes:
   ```bash
   php bin/console cache:clear
   ```
   
   - `/_examples/validation/user` - User validation example
   - `/_examples/validation/user-dto` - DTO injection example
   - `/_examples/validation/profile` - Profile creation example

4. Explore the source code in the bundle:
   - Controllers: `vendor/outcomer/symfony-json-schema-validation/src/Examples/Controller/`
   - Schemas: `vendor/outcomer/symfony-json-schema-validation/src/Examples/Schemas/`
   - DTOs: `vendor/outcomer/symfony-json-schema-validation/src/Examples/Model/`

These examples demonstrate real implementations you can use as reference when building your own API.

## Basic User Registration

**Schema:** `config/validation/schemas/user-register.json`

```json
{
  "$schema": "https://json-schema.org/draft/2020-12/schema",
  "description": "User registration endpoint",
  "type": "object",
  "properties": {
    "body": {
      "type": "object",
      "properties": {
        "email": {
          "type": "string",
          "format": "email",
          "description": "User email address"
        },
        "password": {
          "type": "string",
          "minLength": 8,
          "description": "User password (min 8 characters)"
        },
        "name": {
          "type": "string",
          "minLength": 2,
          "maxLength": 100,
          "description": "User full name"
        }
      },
      "required": ["email", "password", "name"]
    }
  }
}
```

**Controller:**

```php
<?php

namespace App\Controller;

use App\Dto\UserRegisterDto;
use Outcomer\ValidationBundle\Attribute\MapRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    #[Route('/api/auth/register', methods: ['POST'])]
    public function register(#[MapRequest('user-register.json')] UserRegisterDto $dto): JsonResponse
    {
        $user = $this->userService->register(
            email: $dto->email,
            password: $dto->password,
            name: $dto->name
        );
        
        return $this->json([
            'id' => $user->getId(),
            'message' => 'Registration successful'
        ], 201);
    }
}
```

**DTO:**

```php
<?php

namespace App\Dto;

use Outcomer\ValidationBundle\Model\ValidatedDtoInterface;

readonly class UserRegisterDto implements ValidatedDtoInterface
{
    public function __construct(
        public string $email,
        public string $password,
        public string $name,
        public array $violations = [],
    ) {}
    
    public static function fromPayload(Payload $payload, array $violations = []): static
    {
        $data = $payload->getContent();
        
        return new static(
            $data['email'],
            $data['password'],
            $data['name'],
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

## Product Search with Filters

**Schema:** `config/validation/schemas/product-search.json`

```json
{
  "type": "object",
  "properties": {
    "query": {
      "type": "object",
      "properties": {
        "q": {
          "type": "string",
          "minLength": 1,
          "description": "Search query"
        },
        "category": {
          "type": "string",
          "enum": ["electronics", "clothing", "books", "food"],
          "description": "Product category filter"
        },
        "minPrice": {
          "type": "number",
          "minimum": 0,
          "description": "Minimum price filter"
        },
        "maxPrice": {
          "type": "number",
          "minimum": 0,
          "description": "Maximum price filter"
        },
        "page": {
          "type": "integer",
          "minimum": 1,
          "default": 1
        },
        "limit": {
          "type": "integer",
          "minimum": 1,
          "maximum": 100,
          "default": 20
        }
      },
      "required": ["q"]
    }
  }
}
```

**Controller:**

```php
#[Route('/api/products/search', methods: ['GET'])]
public function search(#[MapRequest('product-search.json')] ProductSearchDto $dto): JsonResponse
{
    $results = $this->productRepository->search(
        query: $dto->q,
        category: $dto->category,
        minPrice: $dto->minPrice,
        maxPrice: $dto->maxPrice,
        page: $dto->page,
        limit: $dto->limit
    );
    
    return $this->json($results);
}
```

## API with Authentication Header

**Schema:** `config/validation/schemas/protected-action.json`

```json
{
  "type": "object",
  "properties": {
    "headers": {
      "type": "object",
      "properties": {
        "authorization": {
          "type": "string",
          "pattern": "^Bearer [A-Za-z0-9-._~+/]+=*$",
          "description": "JWT Bearer token"
        }
      },
      "required": ["authorization"]
    },
    "body": {
      "type": "object",
      "properties": {
        "action": {
          "type": "string",
          "enum": ["approve", "reject", "pending"]
        },
        "comment": {
          "type": "string",
          "maxLength": 500
        }
      },
      "required": ["action"]
    }
  }
}
```

**Controller:**

```php
#[Route('/api/requests/{id}/action', methods: ['POST'])]
public function takeAction(string $id, #[MapRequest('protected-action.json')] ActionDto $dto): JsonResponse
{
    // Authorization header is validated
    // Extract and verify token in your security layer
    
    $this->requestService->takeAction($id, $dto->action, $dto->comment);
    
    return $this->json(['status' => 'success']);
}
```

## Complex Nested Object

**Schema:** `config/validation/schemas/order-create.json`

```json
{
  "type": "object",
  "properties": {
    "body": {
      "type": "object",
      "properties": {
        "customer": {
          "type": "object",
          "properties": {
            "name": {"type": "string"},
            "email": {"type": "string", "format": "email"},
            "phone": {"type": "string", "pattern": "^\\+?[1-9]\\d{1,14}$"}
          },
          "required": ["name", "email"]
        },
        "items": {
          "type": "array",
          "minItems": 1,
          "items": {
            "type": "object",
            "properties": {
              "productId": {"type": "string", "format": "uuid"},
              "quantity": {"type": "integer", "minimum": 1},
              "price": {"type": "number", "minimum": 0}
            },
            "required": ["productId", "quantity", "price"]
          }
        },
        "shipping": {
          "type": "object",
          "properties": {
            "address": {"type": "string"},
            "city": {"type": "string"},
            "zipCode": {"type": "string"},
            "country": {"type": "string", "minLength": 2, "maxLength": 2}
          },
          "required": ["address", "city", "zipCode", "country"]
        }
      },
      "required": ["customer", "items", "shipping"]
    }
  }
}
```

**DTOs:**

```php
readonly class CustomerDto
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $phone = null,
    ) {}
}

readonly class OrderItemDto
{
    public function __construct(
        public string $productId,
        public int $quantity,
        public float $price,
    ) {}
}

readonly class ShippingDto
{
    public function __construct(
        public string $address,
        public string $city,
        public string $zipCode,
        public string $country,
    ) {}
}

readonly class CreateOrderDto implements ValidatedDtoInterface
{
    /**
     * @param OrderItemDto[] $items
     */
    public function __construct(
        public CustomerDto $customer,
        public array $items,
        public ShippingDto $shipping,
        public array $violations = [],
    ) {}
    
    public static function fromPayload(Payload $payload, array $violations = []): static
    {
        $data = $payload->getContent();
        
        $customer = new CustomerDto(
            $data['customer']['name'],
            $data['customer']['email']
        );
        
        $items = array_map(
            fn($item) => new OrderItemDto(
                $item['productId'],
                $item['quantity'],
                $item['price']
            ),
            $data['items']
        );
        
        $shipping = new ShippingDto(
            $data['shipping']['address'],
            $data['shipping']['city'],
            $data['shipping']['zipCode'],
            $data['shipping']['country']
        );
        
        return new static($customer, $items, $shipping, $violations);
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

## File Upload with Metadata

**Schema:** `config/validation/schemas/file-upload.json`

```json
{
  "type": "object",
  "properties": {
    "body": {
      "type": "object",
      "properties": {
        "title": {
          "type": "string",
          "minLength": 1,
          "maxLength": 200
        },
        "description": {
          "type": "string",
          "maxLength": 1000
        },
        "tags": {
          "type": "array",
          "items": {"type": "string"},
          "maxItems": 10
        },
        "visibility": {
          "type": "string",
          "enum": ["public", "private", "unlisted"],
          "default": "private"
        }
      },
      "required": ["title"]
    }
  }
}
```

**Controller:**

```php
#[Route('/api/files/upload', methods: ['POST'])]
public function upload(#[MapRequest('file-upload.json')] FileUploadDto $dto, Request $request): JsonResponse
{
    $file = $request->files->get('file');
    
    $uploadedFile = $this->fileService->upload(
        file: $file,
        title: $dto->title,
        description: $dto->description,
        tags: $dto->tags,
        visibility: $dto->visibility
    );
    
    return $this->json(['id' => $uploadedFile->getId()], 201);
}
```

## Next Steps

- **[How It Works →](../guide/how-it-works)** - Understand the philosophy
- **[Schema Basics →](../guide/schema-basics)** - JSON Schema fundamentals
- **[DTO Injection →](../guide/dto-injection)** - Type-safe DTOs
