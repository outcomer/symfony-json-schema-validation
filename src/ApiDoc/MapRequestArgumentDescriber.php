<?php
/**
 * This file is part of the Outcomer Symfony Validation package.
 *
 * (c) David Evdoshchenko <773021792e@gmail.com>
 *
 * @package Outcomer\Validation
 */

declare(strict_types=1);

namespace Outcomer\ValidationBundle\ApiDoc;

use Outcomer\ValidationBundle\Attribute\MapRequest;
use Outcomer\ValidationBundle\Helpers\Arrays;
use Outcomer\ValidationBundle\Helpers\Schema;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use Nelmio\ApiDocBundle\RouteDescriber\RouteArgumentDescriber\RouteArgumentDescriberInterface;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * NelmioApiDoc describer for MapRequest attributes that generates OpenAPI documentation
 */
final class MapRequestArgumentDescriber implements RouteArgumentDescriberInterface
{
	private Schema $schema;
	private string $currentSchemaDir;

	public function __construct(private readonly string $schemasPath)
	{
		$this->schema = new Schema($schemasPath);
	}

	/**
	 * Describes OpenAPI operation by analyzing MapRequest attributes
	 */
	public function describe(ArgumentMetadata $argumentMetadata, OA\Operation $operation): void
	{
		$attribute = $argumentMetadata->getAttributes(MapRequest::class)[0] ?? null;

		if (!$attribute) {
			return;
		}

		$schemaPath = $this->schema->findSchemaFile($attribute->schema);

		if (!file_exists($schemaPath)) {
			return;
		}

		$schemaContent = file_get_contents($schemaPath);
		$jsonSchema    = json_decode(json: $schemaContent, associative: true);

		if (!$jsonSchema) {
			return;
		}

		// Store original schema directory for relative $ref resolution
		$this->currentSchemaDir = dirname($schemaPath);

		// Resolve all $ref recursively (including root level and nested)
		$resolvedSchema = $this->resolveAllRefs($jsonSchema);

		// Sort properties alphabetically for better API documentation
		if (isset($resolvedSchema['properties']['body']['properties'])) {
			Arrays::sortArrayByKeys($resolvedSchema['properties']['body']['properties']);
		}
		if (isset($resolvedSchema['properties']['query']['properties'])) {
			Arrays::sortArrayByKeys($resolvedSchema['properties']['query']['properties']);
		}

		if (isset($resolvedSchema['properties']['body']) && 'null' !== $resolvedSchema['properties']['body']['type']) {
			$this->addRequestBody(operation: $operation, bodySchema: $resolvedSchema['properties']['body']);
		}

		// Add parameters from path and query parts
		if (isset($resolvedSchema['properties']['path']['properties'])) {
			foreach ($resolvedSchema['properties']['path']['properties'] as $name => $property) {
				$this->addParameter(
					operation: $operation,
					name: $name,
					in: 'path',
					property: $property,
					required: in_array(needle: $name, haystack: $resolvedSchema['properties']['path']['required'] ?? [])
				);
			}
		}

		if (isset($resolvedSchema['properties']['query']['properties'])) {
			foreach ($resolvedSchema['properties']['query']['properties'] as $name => $property) {
				$this->addParameter(
					operation: $operation,
					name: $name,
					in: 'query',
					property: $property,
					required: in_array(needle: $name, haystack: $resolvedSchema['properties']['query']['required'] ?? [])
				);
			}
		}
	}

	/**
	 * Adds request body to OpenAPI operation from schema
	 */
	private function addRequestBody(OA\Operation $operation, array $bodySchema): void
	{
		if ($operation->requestBody === Generator::UNDEFINED) {
			$operation->requestBody = new OA\RequestBody(['request' => "{$operation->operationId}_request"]);
		}

		if ($operation->requestBody->content === Generator::UNDEFINED) {
			$operation->requestBody->content = [];
		}

		$mediaType         = new OA\MediaType(['mediaType' => 'application/json']);
		$mediaType->schema = $this->convertJsonSchemaToOpenApi($bodySchema);

		$operation->requestBody->content['application/json'] = $mediaType;
	}

	/**
	 * Adds parameter to OpenAPI operation
	 */
	private function addParameter(OA\Operation $operation, string $name, string $in, array $property, bool $required): void
	{
		$parameter = Util::getOperationParameter(operation: $operation, name: $name, in: $in);

		Util::modifyAnnotationValue(parameter: $parameter, property: 'required', value: $required);

		/** @var OA\Schema $schema */
		$schema = Util::getChild(parent: $parameter, class: OA\Schema::class);

		$openApiSchema = $this->convertJsonSchemaToOpenApi($property);

		foreach (get_object_vars($openApiSchema) as $key => $value) {
			if (Generator::UNDEFINED !== $value) {
				Util::modifyAnnotationValue(parameter: $schema, property: $key, value: $value);
			}
		}
	}

	/**
	 * Converts JSON Schema to OpenAPI Schema object
	 */
	private function convertJsonSchemaToOpenApi(array $jsonSchema): OA\Schema
	{
		// Remove Opis-specific keys (no need to resolve refs again, already done in describe())
		$cleanedSchema = $this->removeOpisSpecificKeys($jsonSchema);

		$schema = new OA\Schema([
			'schema' => "inline_schema_".uniqid(),
		]);

		// Handle properties separately to create OA\Property objects
		if (isset($cleanedSchema['properties'])) {
			$schema->properties = $this->convertPropertiesToOpenApi($cleanedSchema['properties']);
			unset($cleanedSchema['properties']);
		}

		// Handle items for arrays
		if (isset($cleanedSchema['items'])) {
			$schema->items = $this->convertJsonSchemaToOpenApi($cleanedSchema['items']);
			unset($cleanedSchema['items']);
		}

		// Handle oneOf, anyOf, allOf
		foreach (['oneOf', 'anyOf', 'allOf'] as $combiner) {
			if (isset($cleanedSchema[$combiner])) {
				$schema->$combiner = [];
				foreach ($cleanedSchema[$combiner] as $subSchema) {
					$schema->$combiner[] = $this->convertJsonSchemaToOpenApi($subSchema);
				}
				unset($cleanedSchema[$combiner]);
			}
		}

		// Copy all other attributes from cleaned schema
		foreach ($cleanedSchema as $key => $value) {
			$schema->$key = $value;
		}

		return $schema;
	}

	/**
	 * Converts properties array to OpenAPI Property objects
	 */
	private function convertPropertiesToOpenApi(array $properties): array
	{
		$result = [];
		foreach ($properties as $propName => $propSchema) {
			$property = new OA\Property(['property' => $propName]);

			// Handle nested properties recursively
			if (isset($propSchema['properties'])) {
				$property->properties = $this->convertPropertiesToOpenApi($propSchema['properties']);
				unset($propSchema['properties']);
			}

			// Handle items for arrays
			if (isset($propSchema['items'])) {
				$property->items = $this->convertJsonSchemaToOpenApi($propSchema['items']);
				unset($propSchema['items']);
			}

			// Handle oneOf, anyOf, allOf
			foreach (['oneOf', 'anyOf', 'allOf'] as $combiner) {
				if (isset($propSchema[$combiner])) {
					$property->$combiner = [];
					foreach ($propSchema[$combiner] as $subSchema) {
						$property->$combiner[] = $this->convertJsonSchemaToOpenApi($subSchema);
					}
					unset($propSchema[$combiner]);
				}
			}

			// Copy all other attributes
			foreach ($propSchema as $key => $value) {
				$property->$key = $value;
			}

			$result[] = $property;
		}

		return $result;
	}

	/**
	 * Recursively resolves all $ref in the schema
	 */
	private function resolveAllRefs(array $schema): array
	{
		$result = [];
		$inject = null;

		foreach ($schema as $key => $value) {
			if ('$ref' === $key && str_ends_with($value, '.json')) {
				// Replace $ref with resolved schema content
				$refFileName = basename(parse_url($value, PHP_URL_PATH));
				$refPath     = $this->schemasPath."/{$refFileName}";
				$refContent  = file_get_contents($refPath);

				$resolvedSchema = json_decode($refContent, true);

				if ($resolvedSchema) {
					// Recursively resolve any refs in the resolved schema
					$resolvedSchema = $this->resolveAllRefs($resolvedSchema);

					// Store $inject for later application
					if (isset($schema['$inject'])) {
						$inject = $schema['$inject'];
					}

					// Merge resolved schema, excluding the $ref itself
					foreach ($resolvedSchema as $resolvedKey => $resolvedValue) {
						if ('$ref' !== $resolvedKey && '$inject' !== $resolvedKey) {
							$result[$resolvedKey] = $resolvedValue;
						}
					}
				}
			} elseif ('$inject' === $key) {
				// Skip $inject here, will be applied after resolution
				continue;
			} elseif (is_array($value)) {
				// Recursively resolve refs in nested arrays
				$result[$key] = $this->resolveAllRefs($value);
			} else {
				$result[$key] = $value;
			}
		}

		// Apply $inject if it was found
		if (null !== $inject) {
			foreach ($inject as $slotKey => $slotValue) {
				$result = $this->applyInject($result, $slotKey, $slotValue);
			}
		}

		return $result;
	}

	/**
	 * Removes Opis-specific keys from schema
	 */
	private function removeOpisSpecificKeys(array $schema): array
	{
		$cleaned = [];
		foreach ($schema as $key => $value) {
			// Skip Opis-specific keys
			if ('$filters' === $key || '$schema' === $key) {
				continue;
			}

			// Recursively clean nested objects and arrays
			if (is_array($value)) {
				$cleaned[$key] = $this->removeOpisSpecificKeys($value);
			} else {
				$cleaned[$key] = $value;
			}
		}

		return $cleaned;
	}

	/**
	 * Applies $inject values to schema slots
	 */
	private function applyInject(array $schema, string $key, array $value): array
	{
		// Find schema slots and replace specific slot
		return $this->replaceInArray(array: $schema, replacer: function ($item) use ($key, $value) {
			if (is_array($item) && isset($item['$slots']) && isset($item['$slots'][$key])) {
				// Replace entire object with content from $inject
				$result = $item;
				unset($result['$slots']);
				// Merge with passed value, and resolve any refs in the injected value
				foreach ($value as $k => $v) {
					$result[$k] = is_array($v) ? $this->resolveAllRefs($v) : $v;
				}

				return $result;
			}

			return $item;
		});
	}

	/**
	 * Recursively replaces array elements using a replacer function
	 */
	private function replaceInArray(array $array, callable $replacer): array
	{
		$result = [];
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$replaced = $replacer($value);
				if ($replaced !== $value) {
					$result[$key] = $replaced;
				} else {
					$result[$key] = $this->replaceInArray(array: $value, replacer: $replacer);
				}
			} else {
				$result[$key] = $value;
			}
		}

		return $result;
	}
}
