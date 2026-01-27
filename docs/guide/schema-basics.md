# Schema Basics

## JSON Schema Standard Compliance

This bundle **strictly follows** the [JSON Schema specification](https://json-schema.org/) without inventing custom extensions or proprietary syntax. You can use **all features** defined in the official JSON Schema spec.

### Validation Engine

Under the hood, validation is powered by [Opis JSON Schema](https://opis.io/json-schema/) - a robust, spec-compliant PHP implementation. This means:

- ✅ Full support for JSON Schema Draft 2020-12, 2019-09, and Draft-07
- ✅ All standard keywords: `type`, `properties`, `required`, `pattern`, `format`, etc.
- ✅ Schema composition: `allOf`, `anyOf`, `oneOf`, `not`
- ✅ Conditional schemas: `if-then-else`
- ✅ References: `$ref`, `$defs`
- ✅ Format validation: `email`, `uri`, `uuid`, `date-time`, etc.

**No vendor lock-in.** Your schemas are portable and can be used with any JSON Schema validator.

### Bundle-Specific Extension: Filters

The **only** addition beyond the JSON Schema spec is the `$filters` keyword for dynamic validation:

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

**Important:** Filters **do not modify data**. They return `true` (valid) or `false` (invalid) and are used for dynamic validations like:
- Checking uniqueness in database
- Verifying external API constraints
- Custom business logic validation

Filters must implement `Opis\JsonSchema\Filter` interface from the Opis JSON Schema library.

See [Configuration →](./configuration#filters) for details on implementing filters.

## Request Structure

The bundle normalizes all HTTP request components into a unified JSON structure:

```json
{
  "body": {},      // Request body (parsed JSON)
  "query": {},     // URL query parameters
  "path": {},      // Route path parameters
  "headers": {}    // HTTP headers
}
```

Your JSON Schema validates this entire structure, allowing you to define validation rules for any part of the request.

## Basic Schema Example

```json
{
  "$schema": "https://json-schema.org/draft/2020-12/schema",
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
    }
  }
}
```

## Common Validation Rules

### String Validation

```json
{
  "type": "string",
  "minLength": 3,
  "maxLength": 100,
  "pattern": "^[a-zA-Z]+$",
  "format": "email"  // or "uri", "date-time", "uuid", etc.
}
```

### Number Validation

```json
{
  "type": "integer",
  "minimum": 0,
  "maximum": 100,
  "multipleOf": 5
}
```

### Array Validation

```json
{
  "type": "array",
  "items": {
    "type": "string"
  },
  "minItems": 1,
  "maxItems": 10,
  "uniqueItems": true
}
```

### Object Validation

```json
{
  "type": "object",
  "properties": {
    "name": {"type": "string"},
    "age": {"type": "integer"}
  },
  "required": ["name"],
  "additionalProperties": false
}
```

## Validating Headers

```json
{
  "properties": {
    "headers": {
      "type": "object",
      "properties": {
        "authorization": {
          "type": "string",
          "pattern": "^Bearer .+"
        },
        "x-api-version": {
          "type": "string",
          "enum": ["v1", "v2"]
        }
      },
      "required": ["authorization"]
    }
  }
}
```

## Validating Query Parameters

```json
{
  "properties": {
    "query": {
      "type": "object",
      "properties": {
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
        },
        "sort": {
          "type": "string",
          "enum": ["asc", "desc"]
        }
      }
    }
  }
}
```

## Validating Path Parameters

```json
{
  "properties": {
    "path": {
      "type": "object",
      "properties": {
        "id": {
          "type": "string",
          "format": "uuid"
        }
      },
      "required": ["id"]
    }
  }
}
```

## Schema Composition

### Using $ref

```json
{
  "definitions": {
    "email": {
      "type": "string",
      "format": "email"
    }
  },
  "properties": {
    "body": {
      "type": "object",
      "properties": {
        "email": {"$ref": "#/definitions/email"}
      }
    }
  }
}
```

### Using allOf

```json
{
  "allOf": [
    {
      "type": "object",
      "properties": {
        "name": {"type": "string"}
      }
    },
    {
      "type": "object",
      "properties": {
        "age": {"type": "integer"}
      }
    }
  ]
}
```

## Next Steps

- **[Configuration →](./configuration)** - Learn about bundle configuration options
- **[DTO Injection →](./dto-injection)** - Use custom DTOs with validated data
- **[Examples →](../examples/)** - See real-world schema examples
