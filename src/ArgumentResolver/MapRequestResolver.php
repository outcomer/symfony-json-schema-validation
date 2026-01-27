<?php

/**
 * This file is part of the Outcomer Symfony Validation package.
 *
 * (c) David Evdoshchenko <773021792e@gmail.com>
 *
 * @package Outcomer\Validation
 */

declare(strict_types=1);

namespace Outcomer\ValidationBundle\ArgumentResolver;

use InvalidArgumentException;
use Outcomer\ValidationBundle\Attribute\MapRequest;
use Outcomer\ValidationBundle\Exception\ValidationException;
use Outcomer\ValidationBundle\Helpers\Schema;
use Outcomer\ValidationBundle\Helpers\Types;
use Outcomer\ValidationBundle\Model\Payload;
use Outcomer\ValidationBundle\Model\ValidatedDtoInterface;
use Outcomer\ValidationBundle\Model\ValidatedRequest;
use Outcomer\ValidationBundle\Schema\SchemaValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Argument resolver for validating HTTP requests and returning Payload objects
 */
final class MapRequestResolver implements ValueResolverInterface
{
    private Schema $schema;

    public function __construct(private readonly SchemaValidator $validator, private readonly string $schemasPath)
    {
        $this->schema = new Schema($schemasPath);
    }

    /**
     * Resolves request arguments by validating against JSON schema
     */
    public function resolve(Request $request, ArgumentMetadata $argument): array
    {
        $attribute = $argument->getAttributesOfType(MapRequest::class)[0] ?? null;

        if (!$attribute) {
            return [];
        }

        // Clean path parameters from internal Symfony attributes
        $pathParams = $request->attributes->all();
        $cleanPath  = [];
        foreach ($pathParams as $key => $value) {
            // Exclude Symfony system parameters
            if (!str_starts_with(haystack: $key, needle: '_') && !is_object($value)) {
                // Try to convert numeric strings to numbers
                if (is_string($value) && is_numeric($value)) {
                    $cleanPath[$key] = str_contains(haystack: $value, needle: '.') ? (float) $value : (int) $value;
                } else {
                    $cleanPath[$key] = $value;
                }
            }
        }

        $requestContent = $request->getContent();
        $bodyData       = empty($requestContent) ? null : json_decode(json: $requestContent, associative: false);

        // Extract and normalize headers
        $headers = [];
        foreach ($request->headers->all() as $key => $values) {
            // Use first value for single headers, array for multiple values
            $headers[strtolower($key)] = count($values) === 1 ? $values[0] : $values;
        }

        $data = (object) [
            'body'    => $bodyData,
            'query'   => (object) Types::castTypes($request->query->all()),
            'path'    => (object) Types::castTypes($cleanPath),
            'headers' => (object) Types::castTypes($headers),
        ];

        $schemaPath = $this->schema->findSchemaFile($attribute->schema);
        $payload    = new Payload(body: $data->body, query: $data->query, path: $data->path, headers: $data->headers);
        $violations = [];

        try {
            $this->validator->validateBySchemaFile(data: $data, schemaPath: $schemaPath);
        } catch (ValidationException $e) {
            if ($attribute->triggerResponse) {
                throw $e;
            }
            $violations = $e->getValidationErrors();
        }

        $parameterType = $argument->getType();

        // For MapRequest attribute, parameter type must be an existing class implementing ValidatedDtoInterface
        if ($parameterType && !class_exists($parameterType)) {
            throw new InvalidArgumentException(sprintf('MapRequest parameter type "%s" must be an existing class', $parameterType));
        }

        if ($parameterType && !is_subclass_of($parameterType, ValidatedDtoInterface::class)) {
            throw new InvalidArgumentException(sprintf('MapRequest parameter type "%s" must implement %s', $parameterType, ValidatedDtoInterface::class));
        }

        // Create DTO via interface contract
        if ($parameterType) {
            return [$parameterType::fromPayload($payload, $violations)];
        }

        // Default fallback when no type specified
        return [ValidatedRequest::fromPayload($payload, $violations)];
    }
}
