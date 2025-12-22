# Outcomer Validation Bundle

A Symfony bundle for JSON Schema validation of HTTP requests with OPIS integration and automatic OpenAPI documentation generation.

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.4-blue.svg)](https://php.net/)
[![Symfony Version](https://img.shields.io/badge/symfony-6.4%20%7C%207.x-green.svg)](https://symfony.com/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

## Why?

In typical Symfony applications, API validation and documentation exist in separate places:
- Validation rules are defined with Symfony constraints in PHP classes
- API specifications are generated from annotations or written separately
- Even with auto-generation, keeping validation logic and API specs synchronized requires manual effort

This creates maintenance overhead and potential inconsistencies between what the API validates and what the documentation describes.

**The solution**: JSON Schema is both a powerful validation standard and fully compatible with OpenAPI 3. This bundle eliminates the synchronization problem by using JSON Schema as the single source of truth for both runtime validation and API documentation generation.

Define validation rules once in JSON Schema → Get automatic request validation + accurate OpenAPI documentation.

## Features

- **JSON Schema Validation** - Validate HTTP requests using JSON Schema with OPIS library
- **Automatic Validation** - Use PHP attributes to validate controller parameters automatically
- **Request Parts Validation** - Validate body, query, and path parameters separately
- **Type Casting** - Automatic type conversion for query and path parameters
- **Custom Filters** - Extend validation with custom OPIS filters
- **OpenAPI Integration** - Automatic OpenAPI documentation generation via NelmioApiDocBundle
- **Schema References** - Support for `$ref` to reuse schemas across your project
- **Flexible Error Handling** - Choose between throwing exceptions or collecting violations

## Requirements

- PHP 8.4 or higher
- Symfony 6.4 or 7.x
- OPIS JSON Schema 2.0

## Installation

Install the bundle via Composer:

```bash
composer require outcomer/validation
```

If you're not using Symfony Flex, register the bundle manually in `config/bundles.php`:

```php
return [
    // ...
    Outcomer\ValidationBundle\OutcomerValidationBundle::class => ['all' => true],
];
```

## Configuration

> **Note**: The Symfony Flex recipe is pending publication to symfony/recipes-contrib. Until then, you need to create the configuration file manually.

### Manual Configuration (Required for now)

Create a configuration file `config/packages/outcomer_validation.yaml`:

```yaml
outcomer_validation:
    # Path to your JSON Schema files
    schemas_path: '%kernel.project_dir%/config/validation/schemas'

    # Base domain for auto-generated schema IDs
    schema_domain: 'https://example.com'

    # Custom filters (optional)
    filters:
        uuid: App\Validation\Filter\UuidFilter
        date: App\Validation\Filter\DateFilter
```

### Configuration Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `schemas_path` | string | `%kernel.project_dir%/config/validation/schemas` | Directory containing your JSON Schema files |
| `schema_domain` | string | `https://example.com` | Base URL for auto-generated schema IDs (used by OPIS for schema resolution) |
| `filters` | array | `[]` | Map of custom filter names to their service classes |

### Future: Automatic Configuration

Once the Symfony Flex recipe is published to symfony/recipes-contrib, the configuration file will be created automatically during installation. You can track the progress in the [Contributing](#contributing) section.

## Usage

### Basic Example

1. Create a JSON Schema file `config/validation/schemas/user-create.json`:

```json
{
    "$schema": "https://json-schema.org/draft-07/schema#",
    "type": "object",
    "properties": {
        "body": {
            "type": "object",
            "properties": {
                "name": {
                    "type": "string",
                    "minLength": 3,
                    "maxLength": 100
                },
                "email": {
                    "type": "string",
                    "format": "email"
                },
                "age": {
                    "type": "integer",
                    "minimum": 18
                }
            },
            "required": ["name", "email"]
        },
        "query": {
            "type": "object",
            "properties": {
                "notify": {
                    "type": "boolean"
                }
            }
        }
    }
}
```

2. Use the `MapRequest` attribute in your controller:

```php
use Outcomer\ValidationBundle\Attribute\MapRequest;
use Outcomer\ValidationBundle\Model\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/users', methods: ['POST'])]
public function createUser(
    #[MapRequest('user-create.json')] Request $request
): JsonResponse {
    // Access validated data
    $payload = $request->getPayload();
    $body = $payload->getBody();    // stdClass with validated body data
    $query = $payload->getQuery();  // stdClass with validated query parameters

    // Your business logic here
    $user = new User(
        name: $body->name,
        email: $body->email,
        age: $body->age ?? null
    );

    return new JsonResponse(['id' => $user->getId()], 201);
}
```

### Non-Blocking Validation

If you want to collect validation errors without throwing exceptions:

```php
#[Route('/api/users', methods: ['POST'])]
public function createUser(
    #[MapRequest('user-create.json', die: false)] Request $request
): JsonResponse {
    if ($request->hasViolations()) {
        return new JsonResponse([
            'errors' => $request->getViolations()
        ], 400);
    }

    // Process valid request
    $payload = $request->getPayload();
    // ...
}
```

### Schema References

You can reuse schemas using `$ref`:

**schemas/definitions/address.json:**
```json
{
    "type": "object",
    "properties": {
        "street": { "type": "string" },
        "city": { "type": "string" },
        "zipCode": { "type": "string", "pattern": "^[0-9]{5}$" }
    },
    "required": ["street", "city", "zipCode"]
}
```

**schemas/user-create.json:**
```json
{
    "type": "object",
    "properties": {
        "body": {
            "type": "object",
            "properties": {
                "name": { "type": "string" },
                "address": { "$ref": "/definitions/address.json" }
            }
        }
    }
}
```

## Custom Filters

Create custom OPIS filters for advanced validation:

```php
namespace App\Validation\Filter;

use Opis\JsonSchema\Filter;

class UuidFilter implements Filter
{
    public const TYPES = ['string'];

    public function validate($data, array $args): bool
    {
        return preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $data
        ) === 1;
    }
}
```

Register the filter in configuration:

```yaml
outcomer_validation:
    filters:
        uuid: App\Validation\Filter\UuidFilter
```

Use in your schema:

```json
{
    "type": "object",
    "properties": {
        "userId": {
            "type": "string",
            "$filters": "uuid"
        }
    }
}
```

## OpenAPI Documentation Integration

The bundle automatically generates OpenAPI documentation when used with [NelmioApiDocBundle](https://github.com/nelmio/NelmioApiDocBundle).

### Setup

1. Install NelmioApiDocBundle:

```bash
composer require nelmio/api-doc-bundle
```

2. The integration is automatic! Your JSON Schemas will be converted to OpenAPI specifications:

- Request body schemas → OpenAPI RequestBody
- Query parameters → OpenAPI query parameters
- Path parameters → OpenAPI path parameters
- All types, formats, and constraints are preserved

### Example

With this controller:

```php
#[Route('/api/users/{id}', methods: ['PUT'])]
public function updateUser(
    #[MapRequest('user-update.json')] Request $request,
    int $id
): JsonResponse {
    // ...
}
```

The Swagger UI will automatically display:
- All request body fields with types and constraints
- Required/optional fields
- Field descriptions
- Example values

## Error Response Format

When validation fails (with `die: true`, which is the default), the bundle throws a `ValidationException` with this format:

```json
{
    "message": "Request data is invalid",
    "errors": [
        {
            "expected": "Value must be at least 3 characters long",
            "received": "Jo"
        }
    ]
}
```

## Type Casting

Query and path parameters are automatically cast to their appropriate types:

- Numeric strings → `int` or `float`
- `"true"` / `"false"` → `boolean`
- Numbers in path parameters → `int` or `float`

Example:
```
GET /api/users?page=2&active=true
```

Results in:
```php
$query->page;    // int(2)
$query->active;  // bool(true)
```

## Advanced Usage

### Direct Schema Validator

You can also use the `SchemaValidator` service directly:

```php
use Outcomer\ValidationBundle\Schema\SchemaValidator;

class MyService
{
    public function __construct(
        private SchemaValidator $validator
    ) {}

    public function validateData(array $data): void
    {
        $this->validator->validate($data, [
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'string']
            ]
        ]);
    }
}
```

### Custom DTOs

You can use custom DTOs instead of the generic `Request` model:

```php
class UserCreateDTO
{
    public static function fromPayload(Payload $payload, array $violations): self
    {
        $dto = new self();
        $dto->name = $payload->getBody()->name;
        $dto->email = $payload->getBody()->email;
        $dto->violations = $violations;
        return $dto;
    }
}

// In controller:
#[Route('/api/users', methods: ['POST'])]
public function createUser(
    #[MapRequest('user-create.json')] UserCreateDTO $dto
): JsonResponse {
    // ...
}
```

## Testing

Run PHP_CodeSniffer to check code standards:

```bash
composer code-sniffer
```

## Versioning

This project uses [Semantic Versioning](https://semver.org/). Versions are managed through Git tags:

```bash
# Create a version tag
git tag -a v1.0.0 -m "Release version 1.0.0"
git push origin v1.0.0
```

After pushing the tag, Packagist will automatically detect the new version.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Publishing Symfony Flex Recipe

To enable automatic configuration when installing via Composer, the bundle includes a Flex recipe in the `recipes/` directory. To make it available to all Symfony users:

1. Fork the [symfony/recipes-contrib](https://github.com/symfony/recipes-contrib) repository
2. Create a directory: `outcomer/validation/1.0/`
3. Copy files from `recipes/` to that directory
4. Submit a Pull Request to symfony/recipes-contrib

Once merged, users will automatically get the configuration file when installing the bundle with Symfony Flex.

## License

This bundle is released under the MIT License. See the [LICENSE](LICENSE) file for details.

## Author

**David Evdoshchenko**
- Email: 773021792e@gmail.com

## Credits

This bundle uses:
- [OPIS JSON Schema](https://github.com/opis/json-schema) for JSON Schema validation
- [Symfony Framework](https://symfony.com/) for the foundation
- [NelmioApiDocBundle](https://github.com/nelmio/NelmioApiDocBundle) for OpenAPI integration (optional)
