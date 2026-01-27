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

/**
 * Helpers class
 */
class Types
{
    /**
     * Convert all numeric values into integer/float and boolean strings to bool.
     *
     * @param mixed $hayStack Array or stdClass to cast values.
     */
    public static function castTypes(mixed $hayStack): mixed
    {
        return match (gettype($hayStack)) {
            'array', 'object' => self::castNestedTypes($hayStack),
            'string' => self::castStringValue($hayStack),
            default => $hayStack,
        };
    }

    /**
     * Try to convert value into integer.
     *
     * @param mixed $value Array or stdClass to cast values.
     */
    public static function castAsInteger(mixed $value): int|false
    {
        return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['default' => false]]);
    }

    /**
     * Try to convert value into boolean.
     *
     * @param mixed $value Array or stdClass to cast values.
     */
    public static function castAsBoolean(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOL, ['options' => ['default' => false]]);
    }

    /**
     * Try to convert value into float.
     *
     * @param mixed $value Array or stdClass to cast values.
     */
    public static function castAsFloat(mixed $value): float|false
    {
        return filter_var($value, FILTER_VALIDATE_FLOAT, ['options' => ['default' => false]]);
    }

    /**
     * Cast nested array or object values recursively
     */
    private static function castNestedTypes(array|object $data): array|object
    {
        foreach ($data as $key => &$value) {
            $value = self::castTypes($value);
        }

        return $data;
    }

    /**
     * Cast string value to appropriate type
     */
    private static function castStringValue(string $value): mixed
    {
        if (is_numeric($value)) {
            $intValue = self::castAsInteger($value);

            return $intValue !== false ? $intValue : self::castAsFloat($value);
        }

        return match (strtolower($value)) {
            'true' => true,
            'false' => false,
            default => $value,
        };
    }
}
