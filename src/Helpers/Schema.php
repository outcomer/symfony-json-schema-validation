<?php
/**
 * This file is part of the Outcomer Symfony Validation package.
 *
 * (c) David Evdoshchenko <773021792e@gmail.com>
 *
 * @package Outcomer\Validation\Helpers
 */

declare(strict_types=1);

namespace Outcomer\ValidationBundle\Helpers;

use InvalidArgumentException;

/**
 * Locates schema files in various directories
 */
final class Schema
{
	public function __construct(private readonly string $schemasPath)
	{
	}

	/**
	 * Find schema file in various locations
	 */
	public function findSchemaFile(string $fileName, ?string $currentSchemaPath = null): string
	{
		// Try relative to schemas path
		$refPath = "{$this->schemasPath}/$fileName";
		if (file_exists($refPath)) {
			return $refPath;
		}

		// Try relative to current schema directory if provided
		if ($currentSchemaPath) {
			$currentDir = dirname($currentSchemaPath);
			$refPath    = "$currentDir/$fileName";
			if (file_exists($refPath)) {
				return $refPath;
			}
		}

		throw new InvalidArgumentException("Schema file not found: $fileName");
	}

	/**
	 * Generate schema ID from file path
	 */
	public function generateSchemaId(string $schemaPath, string $schemaDomain): string
	{
		$realPath        = realpath($schemaPath);
		$realSchemasPath = realpath($this->schemasPath);

		if ($realSchemasPath && str_starts_with($realPath, $realSchemasPath)) {
			$relativePath = substr($realPath, strlen($realSchemasPath) + 1);
		} else {
			$relativePath = basename(dirname($schemaPath)).'/'.basename($schemaPath);
		}

		return rtrim($schemaDomain, '/')."/{$relativePath}";
	}

	/**
	 * Check if reference points to a schema file
	 */
	public function getSchemaFileName(string $refUri): false|string
	{
		$path = parse_url($refUri, PHP_URL_PATH) ?: $refUri;

		if ($path && str_contains($path, '.json')) {
			return $path;
		}

		return false;
	}
}
