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
class Arrays
{
    /**
     * Converts an array (including nested arrays) into a stdClass object graph.
     */
    public static function toObjectGraph(array $data): object
    {
        return json_decode(json_encode($data), false);
    }

    /**
     * Sort array keys.
     *
     * @param array|object $data  What to sort.
     * @param integer      $level Parameter to control the depth at which sorting starts.
     */
    public static function sortArrayByKeys(array|object &$data, int $level = 0): void
    {
        if (0 === $level) {
            // Sort the top level keys.
            if (is_array($data)) {
                ksort($data);
            } elseif (is_object($data)) {
                $dataArr = (array) $data;
                ksort($dataArr);
                $data = (object) $dataArr;
            }
        }
        // Sort the nested keys recursively.
        if ($level > 0) {
            if (is_array($data)) {
                foreach ($data as &$value) {
                    if (is_array($value) || is_object($value)) {
                        self::sortArrayByKeys($value, $level - 1);
                    }
                }
            } elseif (is_object($data)) {
                $dataArr = (array) $data;
                foreach ($dataArr as $key => &$value) {
                    if (is_array($value) || is_object($value)) {
                        self::sortArrayByKeys($value, $level - 1);
                    }
                }
                $data = (object) $dataArr;
            }
        }
    }
}
