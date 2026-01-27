# OpenAPI Integration

The bundle seamlessly integrates with `nelmio/api-doc-bundle` to automatically generate OpenAPI (Swagger) documentation from your JSON Schemas.

## Single Source of Truth

This is the **core philosophy** of the bundle: your JSON Schema is the **single source of truth** for both validation AND documentation.

### How It Works

1. You write **one** JSON Schema file
2. The bundle uses it to **validate** requests
3. The same schema is **embedded directly** into OpenAPI specification
4. Documentation always matches actual validation (impossible to desync)

### Why This Matters

**Traditional approach (prone to errors):**
```php
// Validation rules
#[Assert\NotBlank]
#[Assert\Email]
private string $email;

// Documentation (separate, can drift!)
/**
 * @OA\Property(type="string", format="email")
 */
```

**Our approach (single source of truth):**
```json
{
  "properties": {
    "body": {
      "properties": {
        "email": {
          "type": "string",
          "format": "email",
          "description": "User email address"
        }
      }
    }
  }
}
```

This **one** schema:
- ✅ Validates the request
- ✅ Generates OpenAPI docs
- ✅ Always in sync (physically impossible to diverge)

## OpenAPI 3+ Native Compatibility

**Important:** JSON Schema is **not converted** to OpenAPI - it **IS** part of OpenAPI 3+.

OpenAPI 3.0+ uses JSON Schema natively for request/response bodies:

```yaml
# OpenAPI 3.1 spec
components:
  schemas:
    User:
      # This IS JSON Schema, not a conversion
      type: object
      properties:
        email:
          type: string
          format: email
```

The bundle's `MapRequestArgumentDescriber` embeds your JSON Schema **directly** into the OpenAPI spec.

## Setup

Install nelmio/api-doc-bundle:

```bash
composer require nelmio/api-doc-bundle
```

The integration happens automatically. No additional configuration needed.

## Automatic Documentation

When you use `#[MapRequest]`, the bundle automatically:

1. Detects the JSON Schema file
2. Embeds it into OpenAPI specification
3. Generates request body documentation
4. Adds validation constraints to the docs

### Example

This example demonstrates how JSON Schema validation integrates with OpenAPI documentation:

**Controller:**

```php
<?php

namespace App\Controller;

use App\Dto\UserApiDtoRequest;
use App\Dto\UserApiDtoResponse;
use Nelmio\ApiDocBundle\Attribute\Areas;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Outcomer\ValidationBundle\Attribute\MapRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Areas(['examples'])]
#[OA\Tag('Examples')]
#[Route('/_examples/validation')]
class UserController extends AbstractController
{
    #[OA\Post(
        operationId: 'createUser',
        summary: 'Create a new user',
        description: 'Creates a new user with JSON Schema validation',
        responses: [
            new OA\Response(
                response: 200,
                description: 'User created successfully',
                content: new OA\JsonContent(
                    ref: new Model(type: UserApiDtoResponse::class)
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'errors', type: 'object'),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    #[Route('/api/users', methods: ['POST'])]
    public function createUser(#[MapRequest('user-create.json')] UserApiDtoRequest $user): JsonResponse
    {
        $userData = UserApiDtoResponse::fromArray([
            'name'  => $user->name,
            'email' => $user->email,
            'age'   => $user->age,
        ]);

        return $this->json($userData, 200);
    }
}
```

**JSON Schema (`config/validation/schemas/user-create.json`):**

```json
{
  "$schema": "https://json-schema.org/draft-07/schema#",
  "type": "object",
  "properties": {
    "headers": {
      "type": "object",
      "properties": {
        "authorization": {
          "type": "string",
          "description": "Bearer token for authentication",
          "pattern": "^Bearer .+",
          "example": "Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
        },
        "x-api-version": {
          "type": "string",
          "description": "API version",
          "enum": ["v1", "v2"],
          "example": "v1"
        }
      },
      "additionalProperties": true
    },
    "body": {
      "type": "object",
      "properties": {
        "name": {
          "type": "string",
          "minLength": 3,
          "maxLength": 100,
          "description": "User's full name",
          "example": "Jane Smith"
        },
        "email": {
          "type": "string",
          "format": "email",
          "description": "User's email address",
          "example": "john.doe@example.com"
        },
        "age": {
          "type": "integer",
          "minimum": 21,
          "maximum": 100,
          "description": "User's age (optional)",
          "example": 30
        }
      },
      "required": ["name", "email"],
      "additionalProperties": false
    }
  }
}
```

The schema is automatically embedded in OpenAPI documentation. No duplicate annotations needed.

## Viewing Documentation

To generate and view OpenAPI documentation:

1. **Generate OpenAPI specification** with nelmio:
   ```bash
   php bin/console nelmio:apidoc:dump --format=yaml > api.yaml
   ```

2. **Create a documentation controller**:
   ```php
   <?php
   
   namespace App\Controller;
   
   use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
   use Symfony\Component\HttpFoundation\Response;
   use Symfony\Component\Routing\Attribute\Route;
   
   class DocsController extends AbstractController
   {
       #[Route('/api-docs', name: 'api_docs', methods: ['GET'])]
       public function docs(): Response
       {
           $html = <<<'HTML'
   <!DOCTYPE html>
   <html>
   <head>
       <title>API Documentation</title>
       <meta charset="utf-8"/>
       <meta name="viewport" content="width=device-width, initial-scale=1">
       <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,700|Roboto:300,400,700" rel="stylesheet">
       <style>
           body { margin: 0; padding: 0; }
       </style>
   </head>
   <body>
       <redoc spec-url="/api.yaml"></redoc>
       <script src="https://cdn.redoc.ly/redoc/latest/bundles/redoc.standalone.js"></script>
   </body>
   </html>
   HTML;
   
           return new Response($html);
       }
   
       #[Route('/api.yaml', name: 'api_spec', methods: ['GET'])]
       public function apiSpec(): Response
       {
           $yamlPath = $this->getParameter('kernel.project_dir') . '/api.yaml';
   
           if (!file_exists($yamlPath)) {
               throw $this->createNotFoundException('API specification not found');
           }
   
           $yamlContent = file_get_contents($yamlPath);
   
           return new Response($yamlContent, 200, [
               'Content-Type' => 'text/yaml',
               'Access-Control-Allow-Origin' => '*'
           ]);
       }
   }
   ```

3. **Access your documentation** at `http://localhost/api-docs`

The documentation will display:
- All endpoints with `#[MapRequest]`
- Request/response schemas embedded from JSON Schema
- Validation rules and constraints
- Required headers, query parameters
- Examples and descriptions

![API Documentation Example](/redocly.png)

## Contract-First Development

This bundle enables true **Contract-First Development**:

1. **Design** your API contract (JSON Schema)
2. **Validate** requests against it automatically
3. **Document** it automatically in OpenAPI
4. **Implement** the business logic

The schema is the contract. Code must conform to it.

Benefits:
- API design happens first
- Documentation is always accurate
- Frontend and backend teams work from same contract
- No drift between docs and implementation

## Next Steps

- **[Schema Basics →](./schema-basics)** - Learn JSON Schema syntax
- **[DTO Injection →](./dto-injection)** - Type-safe DTOs
- **[Examples →](./examples)** - Real-world examples
