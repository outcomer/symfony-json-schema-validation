# How It Works

## The Core Philosophy: Single Source of Truth

This bundle is built on one fundamental principle: **your JSON Schema is the single source of truth** for your API contracts.

### The Problem with Traditional Approaches

In most frameworks, you write validation rules separately from documentation:

```php
// Validation in code
class UserCreateDto {
    #[Assert\NotBlank]
    #[Assert\Email]
    private string $email;
}

// Documentation (separate, can drift)
/**
 * @OA\Property(
 *     property="email",
 *     type="string",
 *     format="email",
 *     description="User email"
 * )
 */
```

**The problem:** Two sources of truth can diverge. You change validation, forget to update docs. Your API documentation lies.

### Our Solution: One Schema, Everything Else Follows

With this bundle, you write **one** JSON Schema:

```json
{
  "properties": {
    "body": {
      "properties": {
        "email": {
          "type": "string",
          "format": "email",
          "description": "User email"
        }
      },
      "required": ["email"]
    }
  }
}
```

This **single** schema:
1. ✅ **Validates** incoming requests
2. ✅ **Generates** OpenAPI documentation
3. ✅ **Types** your DTOs
4. ✅ **Always in sync** (physically impossible to diverge)

## JSON Schema = OpenAPI 3+ (Not Conversion!)

**Critical understanding:** We don't "convert" JSON Schema to OpenAPI. 

OpenAPI 3.0+ **natively uses** JSON Schema for request/response bodies:

```yaml
# This IS the OpenAPI 3.1 specification format
requestBody:
  content:
    application/json:
      schema:
        # This is JSON Schema, part of OpenAPI spec
        type: object
        properties:
          email:
            type: string
            format: email
```

When you use `#[MapRequest('user.json')]`, the bundle:
1. Reads your JSON Schema
2. **Embeds it directly** into OpenAPI spec (no transformation)
3. Uses the **same schema** to validate requests

**Result:** Documentation and validation use the **exact same schema object**. They cannot desync.

## Request-Level Validation

Unlike other frameworks that validate only request body, this bundle validates the **entire HTTP request** as one unified contract:

```json
{
  "type": "object",
  "properties": {
    "body": {
      "type": "object",
      "properties": {
        "name": {"type": "string"}
      }
    },
    "query": {
      "type": "object",
      "properties": {
        "page": {"type": "integer", "minimum": 1}
      }
    },
    "path": {
      "type": "object",
      "properties": {
        "id": {"type": "string", "format": "uuid"}
      }
    },
    "headers": {
      "type": "object",
      "properties": {
        "authorization": {"type": "string", "pattern": "^Bearer .+"}
      }
    }
  }
}
```

One schema defines the **complete request contract**: body + query + path + headers.

## Contract-First Development

This bundle enables true **Contract-First Development**:

### Traditional Flow (Code-First):
1. Write code with validation annotations
2. Generate documentation from code
3. Hope docs match reality
4. Frontend sees outdated docs, breaks

### Our Flow (Contract-First):
1. **Design** API contract (JSON Schema) - discuss with frontend team
2. **Validate** all requests automatically against contract
3. **Document** automatically in OpenAPI (same schema)
4. **Implement** business logic

**The schema is law.** Code must conform to it. Documentation reflects it exactly.

## How MapRequest Works

When you write:

```php
#[Route('/api/users', methods: ['POST'])]
public function create(#[MapRequest('user-create.json')] UserCreateDto $user): JsonResponse
{
    // $user is validated and typed
}
```

Here's what happens:

1. **Request arrives** at your controller
2. **Before** your method executes, bundle intercepts it
3. **Normalizes** request into unified structure:
   ```php
   [
       'body' => /* parsed JSON */,
       'query' => /* query params */,
       'path' => /* route params */,
       'headers' => /* headers */
   ]
   ```
4. **Validates** against `user-create.json` schema
5. **If valid:** Creates `UserCreateDto` with validated data
6. **If invalid:** Returns 400 JSON error response (your method never runs)
7. **Your method** receives guaranteed-valid typed DTO

## Guaranteed Documentation Accuracy

The `MapRequestArgumentDescriber` (when nelmio/api-doc-bundle is installed):

1. Detects `#[MapRequest]` attribute
2. Loads the referenced JSON Schema
3. **Embeds exact same schema** into OpenAPI specification
4. Swagger UI displays it

**Physical guarantee:** The schema shown in docs is **byte-for-byte identical** to the schema used for validation. 

It's not a copy. It's not a conversion. It's **the same file**.

## Why This Matters

### Before (Traditional):
- ❌ Validation rules in PHP annotations
- ❌ Documentation in PHPDoc/OpenAPI annotations  
- ❌ Can drift apart
- ❌ No single source of truth
- ❌ Frontend gets wrong expectations

### After (This Bundle):
- ✅ One JSON Schema file
- ✅ Validates requests
- ✅ Generates docs
- ✅ Types DTOs
- ✅ Cannot drift (same file)
- ✅ Frontend and backend work from identical contract

## Type Safety Bonus

Because validation happens **before** your code runs:

```php
public function create(#[MapRequest('user.json')] UserCreateDto $user): JsonResponse
{
    // $user->email is GUARANTEED to be:
    // - present (if required in schema)
    // - a string (if type: string in schema)
    // - valid email format (if format: email in schema)
    
    // No null checks needed!
    // No type checks needed!
    // Schema already validated everything!
}
```

Your code works with **pre-validated, typed data**. No defensive programming needed.

## Next Steps

Now that you understand the philosophy:

- **[Installation →](./installation)** - Set up the bundle
- **[Quick Start →](./quick-start)** - Your first schema in 5 minutes
- **[Schema Basics →](./schema-basics)** - Learn JSON Schema syntax
- **[OpenAPI Integration →](./openapi-integration)** - See automatic documentation in action
