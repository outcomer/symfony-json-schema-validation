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
        // Try as absolute path first
        if (file_exists($fileName)) {
            return $fileName;
        }

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
}
