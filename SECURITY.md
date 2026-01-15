# Security Policy

## Supported Versions

We release patches for security vulnerabilities for the following versions:

| Version | Supported          |
| ------- | ------------------ |
| 1.x     | :white_check_mark: |

## Reporting a Vulnerability

We take security vulnerabilities seriously. If you discover a security vulnerability within this bundle, please follow these steps:

### How to Report

**Please do NOT report security vulnerabilities through public GitHub issues.**

Instead, please report them via email to:

**Email**: 773021792e@gmail.com

### What to Include

Please include the following information in your report:

- **Type of vulnerability** (e.g., XSS, SQL injection, schema injection, etc.)
- **Full paths of source file(s)** related to the vulnerability
- **Location of the affected source code** (tag/branch/commit or direct URL)
- **Step-by-step instructions** to reproduce the issue
- **Proof-of-concept or exploit code** (if possible)
- **Impact of the vulnerability** - what an attacker might be able to achieve
- **Suggested fix** (if you have one)

### What to Expect

After you submit a report:

1. **Acknowledgment**: We will acknowledge receipt of your vulnerability report within 48 hours
2. **Assessment**: We will assess the vulnerability and determine its impact and severity
3. **Timeline**: We will provide an estimated timeline for a fix
4. **Updates**: We will keep you informed about our progress
5. **Credit**: With your permission, we will credit you in the security advisory when the fix is released

### Disclosure Policy

- **Coordinated disclosure**: Please give us reasonable time to address the vulnerability before public disclosure
- We aim to address critical vulnerabilities within 7 days
- We aim to address other vulnerabilities within 30 days
- We will notify you when the vulnerability is fixed
- We will publish a security advisory on GitHub

## Security Best Practices

When using this bundle, please follow these security best practices:

### 1. Schema Validation

- **Always validate user input** using JSON Schema before processing
- **Use strict type checking** in your schemas
- **Avoid overly permissive schemas** - be specific about allowed values
- **Set appropriate limits** - use `minLength`, `maxLength`, `minimum`, `maximum`

Example of secure schema:
```json
{
  "type": "object",
  "properties": {
    "email": {
      "type": "string",
      "format": "email",
      "maxLength": 255
    },
    "age": {
      "type": "integer",
      "minimum": 0,
      "maximum": 150
    }
  },
  "required": ["email"],
  "additionalProperties": false
}
```

### 2. Schema References

- **Validate schema file paths** - ensure schemas are loaded from trusted locations only
- **Use absolute paths** or configuration-defined paths
- **Don't allow user input** to determine which schema to load
- **Review external schema references** (`$ref`) carefully

### 3. Custom Filters

- **Validate filter implementations** - ensure custom filters don't introduce vulnerabilities
- **Sanitize input** in custom filters before processing
- **Use type hints** and strict typing in filter implementations
- **Test custom filters** thoroughly

### 4. Error Handling

- **Don't expose sensitive information** in validation error messages
- **Log validation failures** for security monitoring
- **Limit error details** in production environments
- **Use generic error messages** for external APIs

### 5. Dependencies

- **Keep dependencies updated** - regularly update Symfony and OPIS JSON Schema
- **Monitor security advisories** for dependencies
- **Use Composer audit** to check for known vulnerabilities:
  ```bash
  composer audit
  ```

### 6. Production Configuration

- **Disable examples** in production:
  ```env
  OUTCOMER_VALIDATION_ENABLE_EXAMPLES=false
  ```

- **Use environment-specific configurations**
- **Enable strict error handling**
- **Set up proper logging**

## Known Security Considerations

### JSON Schema Injection

Be aware that allowing users to provide arbitrary JSON Schemas can be dangerous:

❌ **DON'T** do this:
```php
// Never trust user-provided schema content
$userSchema = $request->get('schema'); // Dangerous!
$validator->validate($data, json_decode($userSchema));
```

✅ **DO** this instead:
```php
// Use predefined, trusted schemas
#[MapRequest('user-create.json')] // Schema from trusted location
```

### Regular Expression DoS (ReDoS)

Be cautious with complex regular expressions in schemas:

❌ **Avoid** complex patterns that can cause catastrophic backtracking:
```json
{
  "type": "string",
  "pattern": "^(a+)+$"  // Can cause ReDoS
}
```

✅ **Use** simple, efficient patterns:
```json
{
  "type": "string",
  "pattern": "^[a-zA-Z0-9_-]{3,20}$"
}
```

### File System Access

The bundle reads JSON Schema files from the filesystem:

- Ensure `schemas_path` points to a secure location
- Don't allow user input to modify the schema path
- Set appropriate file permissions on schema files
- Use read-only access where possible

## Security Updates

Security updates will be released as soon as possible after a vulnerability is confirmed. We recommend:

- **Subscribe to GitHub security advisories** for this repository
- **Enable Dependabot alerts** in your projects
- **Monitor the changelog** for security-related updates
- **Update promptly** when security patches are released

## Questions?

If you have questions about security practices for this bundle:

- Email: 773021792e@gmail.com
- GitHub Discussions: (for general security questions, not vulnerabilities)

Thank you for helping keep this project secure!
