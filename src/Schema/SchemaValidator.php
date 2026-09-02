<?php

/**
 * This file is part of the Outcomer Symfony Validation package.
 *
 * (c) David Evdoshchenko <773021792e@gmail.com>
 *
 * @package Outcomer\Validation
 */

declare(strict_types=1);

namespace Outcomer\ValidationBundle\Schema;

use InvalidArgumentException;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\Resolvers\SchemaResolver;
use Opis\JsonSchema\SchemaLoader;
use Opis\JsonSchema\Validator;
use Outcomer\ValidationBundle\Exception\ValidationException;
use Outcomer\ValidationBundle\Helpers\Arrays;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * JSON Schema validator using OPIS with custom filters support. The loader
 * (parsed-schema cache) is built once and reused across calls; filters are
 * re-registered fresh on every call since their service may not be shared.
 */
final class SchemaValidator
{
    private SchemaFilterResolver $filterResolver;

    private SchemaLoader $loader;

    /**
     * Registered domain -> directory prefixes, keyed by realpath(directory).
     */
    private array $schemaDirs = [];

    public function __construct(private readonly ServiceLocator $filterLocator, private readonly string $schemasPath, private readonly string $schemaDomain, private readonly array $filterMap = [])
    {
        $this->filterResolver = new SchemaFilterResolver();

        $this->loader = new SchemaLoader(
            parser: new SchemaParser(),
            resolver: new SchemaResolver(),
            decodeJsonString: true
        );

        $this->registerSchemaDir(
            domain: $this->schemaDomain,
            path: $this->schemasPath
        );
    }

    /**
     * Registers a domain -> directory prefix on the shared resolver.
     */
    public function registerSchemaDir(string $domain, string $path): void
    {
        $this->loader->resolver()->registerPrefix(sprintf('%s/', rtrim($domain, '/')), $path);
        $this->schemaDirs[realpath($path)] = $domain;
    }

    /**
     * Validates data against an inline schema array built in PHP.
     */
    public function validateInlineSchema(mixed $data, array $schema): void
    {
        $this->validate($data, Arrays::toObjectGraph($schema));
    }

    /**
     * Validates data against a schema file, resolving relative $ref inside it
     * against the file's own directory.
     */
    public function validateFileSchema(mixed $data, string $schemaPath): void
    {
        $schemaContent = file_get_contents(filename: $schemaPath);
        $schemaObject  = json_decode(json: $schemaContent, associative: false);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = json_last_error_msg();
            throw new InvalidArgumentException("Invalid JSON in schema: $error");
        }

        $realDir = dirname(realpath($schemaPath));
        $domain  = $this->domainForDir($realDir);
        $id      = sprintf('%s/%s', rtrim($domain, '/'), basename($schemaPath));

        $this->validate($data, $schemaObject, $id);
    }

    /**
     * Resolves which registered domain a schema file's directory belongs to.
     */
    private function domainForDir(string $realDir): string
    {
        if (isset($this->schemaDirs[$realDir])) {
            return $this->schemaDirs[$realDir];
        }

        throw new InvalidArgumentException("Schema directory '$realDir' is not registered - call registerSchemaDir() for it first");
    }

    /**
     * Validates $data against a decoded schema object, throwing on failure.
     */
    private function validate(mixed $data, object $schemaObject, ?string $id = null): void
    {
        $validator = new Validator($this->loader);

        $validator->setMaxErrors(PHP_INT_MAX);
        $validator->setStopAtFirstError(true);

        $this->registerSchemaFilters($validator, $schemaObject);

        $error = $validator->dataValidation($data, $schemaObject, id: $id);

        if (!is_null($error)) {
            throw new ValidationException($error);
        }
    }

    /**
     * Registers, fresh for this call, every filter the schema declares via $filters/$func.
     */
    private function registerSchemaFilters(Validator $validator, object $schema): void
    {
        $requiredFilters = $this->filterResolver->extract($schema);
        $filterResolver   = $validator->parser()->getFilterResolver();

        foreach ($requiredFilters as $filterName) {
            if (!$this->filterLocator->has($filterName)) {
                throw new InvalidArgumentException("Filter '$filterName' is not registered");
            }

            $filterInstance = $this->filterLocator->get($filterName);

            $types = defined(get_class($filterInstance).'::TYPES')
                ? $filterInstance::TYPES
                : ['string'];

            $filterResolver->registerMultipleTypes(
                name: $filterName,
                filter: $filterInstance,
                types: $types,
            );
        }
    }
}
