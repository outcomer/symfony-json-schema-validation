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

use Outcomer\ValidationBundle\Exception\ValidationException;
use Outcomer\ValidationBundle\Helpers\Schema;
use InvalidArgumentException;
use Opis\JsonSchema\Resolvers\SchemaResolver;
use Opis\JsonSchema\Validator;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * JSON Schema validator using OPIS with custom filters support
 */
final class SchemaValidator
{
	private Validator $validator;
	private array $registeredFilters = [];
	private Schema $schema;

	public function __construct(private readonly ServiceLocator $filterLocator, private readonly string $schemasPath, private readonly string $schemaDomain, private readonly array $filterMap = [])
	{
		$this->validator = new Validator();
		$this->schema    = new Schema($schemasPath);

		$this->configValidator();
	}

	/**
	 * Validates data against a schema array
	 */
	public function validate(array $data, array $schema): void
	{
		$this->registerRequiredFilters($schema);

		$result = $this->validator->validate(data: $data, schema: $schema);

		if (!$result->isValid()) {
			throw new ValidationException($result->error());
		}
	}

	/**
	 * Validates data against a schema file using schema ID resolution
	 */
	public function validateBySchemaFile(mixed $data, string $schemaPath): void
	{
		$schemaContent = file_get_contents(filename: $schemaPath);
		$schemaData    = json_decode(json: $schemaContent, associative: true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			$error = json_last_error_msg();
			throw new InvalidArgumentException("Invalid JSON in schema: $error");
		}

		// Register this schema and all related $ref schemas
		$this->registerSchemaAndReferences(schemaData: $schemaData, schemaPath: $schemaPath);

		// Register filters
		$this->registerRequiredFilters($schemaData);

		// Auto-generate ID from relative path for validation
		$generatedId = $this->schema->generateSchemaId($schemaPath, $this->schemaDomain);
		$result      = $this->validator->validate(data: $data, schema: $generatedId);

		if (!$result->isValid()) {
			throw new ValidationException($result->error());
		}
	}

	private function configValidator(): void
	{
		$this
			->validator
			->setMaxErrors(PHP_INT_MAX)
			->setStopAtFirstError(true);
	}

	/**
	 * Registers required filters for the schema
	 */
	private function registerRequiredFilters(array $schema): void
	{
		$requiredFilters = $this->extractFiltersFromSchema($schema);
		$filters         = $this->validator->parser()->getFilterResolver();

		foreach ($requiredFilters as $filterName) {
			if (!isset($this->registeredFilters[$filterName])) {
				if ($this->filterLocator->has($filterName)) {
					$filterInstance = $this->filterLocator->get($filterName);

					// Get types from filter constant or use default
					$types = defined(get_class($filterInstance).'::TYPES')
						? $filterInstance::TYPES
						: ['string'];

					$filters->registerMultipleTypes(name: $filterName, filter: $filterInstance, types: $types);
					$this->registeredFilters[$filterName] = true;
				} else {
					throw new InvalidArgumentException("Filter '$filterName' is not registered");
				}
			}
		}
	}

	/**
	 * Extracts filter names from schema recursively
	 */
	private function extractFiltersFromSchema(array $schema): array
	{
		$filters = [];
		$this->findFiltersRecursively(data: $schema, filters: $filters);

		return array_unique($filters);
	}

	/**
	 * Recursively finds filters in schema data
	 */
	private function findFiltersRecursively(array $data, array &$filters): void
	{
		foreach ($data as $key => $value) {
			if ('$filters' === $key) {
				if (is_string($value)) {
					$filters[] = $value;
				} elseif (is_array($value)) {
					if (isset($value['$func'])) {
						$filters[] = $value['$func'];
					} else {
						foreach ($value as $filter) {
							if (is_string($filter)) {
								$filters[] = $filter;
							} elseif (is_array($filter) && isset($filter['$func'])) {
								$filters[] = $filter['$func'];
							}
						}
					}
				}
			} elseif ('$error' === $key) {
				// Skip $error key - it's a schema directive, not data to process
				continue;
			} elseif (is_array($value)) {
				$this->findFiltersRecursively(data: $value, filters: $filters);
			}
		}
	}

	/**
	 * Registers schema and its references in OPIS resolver
	 */
	private function registerSchemaAndReferences(array $schemaData, string $schemaPath): void
	{
		$resolver = $this->validator->resolver();

		$registeredSchemas = [];

		// Auto-generate and register ID for main schema using relative path
		$generatedId = $this->schema->generateSchemaId($schemaPath, $this->schemaDomain);
		$resolver->registerFile(id: $generatedId, file: $schemaPath);

		// Recursively register all referenced schemas
		$this->registerReferencesRecursively($schemaData, $schemaPath, $resolver, $registeredSchemas);
	}

	/**
	 * Recursively registers all schema references
	 */
	private function registerReferencesRecursively(array $schemaData, string $currentSchemaPath, SchemaResolver $resolver, array &$registeredSchemas): void
	{
		if (isset($schemaData['$ref'])) {
			$refUri = $schemaData['$ref'];

			$schemaRef = $this->schema->getSchemaFileName($refUri);

			// Skip if not a file reference
			if (false === $schemaRef) {
				return;
			}

			// Skip if already registered (check by schema ref, not full URI)
			if (in_array($schemaRef, $registeredSchemas, true)) {
				return;
			}

			// Parse relative reference like "/Car/schema-search.json" to filename
			$refFileName = ltrim($schemaRef, '/');

			// Find schema file
			$refPath = $this->schema->findSchemaFile($refFileName, $currentSchemaPath);

			if ($refPath && file_exists($refPath)) {
				// Load schema content
				$refSchemaContent = file_get_contents($refPath);
				$refSchemaData    = json_decode($refSchemaContent, true);

				if (json_last_error() === JSON_ERROR_NONE && is_array($refSchemaData)) {
					// Auto-generate ID from relative path
					$generatedId = $this->schema->generateSchemaId($refPath, $this->schemaDomain);

					// Register with auto-generated ID only
					$resolver->registerFile(id: $generatedId, file: $refPath);

					$registeredSchemas[] = $schemaRef;
					$this->registerReferencesRecursively($refSchemaData, $refPath, $resolver, $registeredSchemas);
				}
			}
		}

		// Also check nested arrays for $ref
		foreach ($schemaData as $value) {
			if (is_array($value)) {
				$this->registerReferencesRecursively($value, $currentSchemaPath, $resolver, $registeredSchemas);
			}
		}
	}
}
