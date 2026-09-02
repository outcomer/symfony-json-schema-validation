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

/**
 * Extracts required $filters names from a decoded JSON Schema.
 *
 * Works directly on the object graph produced by json_decode(..., false) -
 * JSON objects become stdClass, JSON arrays stay PHP arrays - so callers
 * don't need a separate associative-array decode just for this.
 */
final class SchemaFilterResolver
{
    /**
     * Extracts unique filter names referenced via $filters in the given schema
     */
    public function extract(object $schema): array
    {
        $filters = [];
        $this->findFiltersRecursively($schema, $filters);

        return array_unique($filters);
    }

    /**
     * Recursively finds filters in schema data
     */
    private function findFiltersRecursively(object|array $data, array &$filters): void
    {
        foreach ($data as $key => $value) {
            if ('$filters' === $key) {
                $this->collectFilterNames($value, $filters);
            } elseif ('$error' === $key) {
                // Skip $error key - it's a schema directive, not data to process
                continue;
            } elseif (is_object($value) || is_array($value)) {
                $this->findFiltersRecursively($value, $filters);
            }
        }
    }

    /**
     * Collects filter names from a $filters value: a single "$func" name,
     * a {"$func": ...} object, or a list mixing either form
     */
    private function collectFilterNames(mixed $filters, array &$names): void
    {
        if (is_string($filters)) {
            $names[] = $filters;
        } elseif ($filters instanceof \stdClass && isset($filters->{'$func'})) {
            $names[] = $filters->{'$func'};
        } elseif (is_array($filters)) {
            foreach ($filters as $filter) {
                $this->collectFilterNames($filter, $names);
            }
        }
    }
}
