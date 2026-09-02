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
use Outcomer\ValidationBundle\Exception\HttpValidationException;
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

    // phpcs:ignore Symfony.Functions.Arguments.Invalid
    public function __construct(
        private readonly SchemaValidator $validator,
        private readonly string $schemasPath,
        private readonly bool $autoCastQuery = true,
        private readonly bool $autoCastPath = true
    ) {
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

        $pathParams = $request->attributes->all();
        $cleanPath  = [];
        foreach ($pathParams as $key => $value) {
            // Exclude Symfony system parameters
            if (!str_starts_with(haystack: $key, needle: '_') && !is_object($value)) {
                $cleanPath[$key] = $value;
            }
        }

        $requestContent = $request->getContent();
        $bodyData       = empty($requestContent) ? null : json_decode(json: $requestContent, associative: false);

        $headers = [];
        foreach ($request->headers->all() as $key => $values) {
            // Use first value for single headers, array for multiple values
            $headers[strtolower($key)] = count($values) === 1 ? $values[0] : $values;
        }

        $queryData = $request->query->all();
        $data      = (object) [
            'body'    => $bodyData,
            'query'   => (object) ($this->autoCastQuery ? Types::castTypes($queryData) : $queryData),
            'path'    => (object) ($this->autoCastPath ? Types::castTypes($cleanPath) : $cleanPath),
            'headers' => (object) $headers,
        ];

        $schemaPath = $this->schema->findSchemaFile($attribute->schema);
        $payload    = new Payload(body: $data->body, query: $data->query, path: $data->path, headers: $data->headers);
        $violations = [];

        try {
            $this->validator->validateFileSchema(data: $data, schemaPath: $schemaPath);
        } catch (ValidationException $e) {
            if ($attribute->triggerResponse) {
                throw new HttpValidationException($e);
            }
            $violations = $e->getValidationErrors();
        }

        $parameterType = $argument->getType();

        if ($parameterType && !class_exists($parameterType)) {
            throw new InvalidArgumentException(sprintf('MapRequest parameter type "%s" must be an existing class', $parameterType));
        }

        if ($parameterType && !is_subclass_of($parameterType, ValidatedDtoInterface::class)) {
            throw new InvalidArgumentException(sprintf('MapRequest parameter type "%s" must implement %s', $parameterType, ValidatedDtoInterface::class));
        }

        if ($parameterType) {
            return [$parameterType::fromPayload($payload, $violations)];
        }

        return [ValidatedRequest::fromPayload($payload, $violations)];
    }
}
