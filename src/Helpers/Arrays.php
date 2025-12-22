<?php
/**
 * This file is part of the Outcomer Symfony Validation package.
 *
 * (c) David Evdoshchenko <773021792e@gmail.com>
 *
 * @package Outcomer\Validation\Helpers
 */

declare(strict_types = 1);

namespace Outcomer\ValidationBundle\Helpers;

use stdClass;

/**
 * Helpers class
 */
class Arrays
{
	/**
	 * Array inserter.
	 *
	 * @param array   $array Target array.
	 * @param integer $index Index in destination after which to insert.
	 * @param array   $value Data to insert at position.
	 */
	public static function insertInArray(array $array, int $index, array $value): array
	{
		return array_slice($array, 0, $index, true) + $value + array_slice($array, $index, count($array) - $index, true);
	}

	/**
	 * Replace keys in array saving keys order.
	 * Recursive.
	 *
	 * @param array $array Original array.
	 */
	public static function toObject(array $array): stdClass
	{
		return json_decode(json_encode($array));
	}

	/**
	 * Groups an array of associative arrays by some key in subarray.
	 *
	 * @param string $key  Property to sort by.
	 * @param array  $data Array that stores multiple associative arrays.
	 */
	public static function groupBy(string $key, array $data): array
	{
		$result = [];

		foreach ($data as $val) {
			if (array_key_exists($key, $val)) {
				$result[$val[$key]][] = $val;
			} else {
				$result[''][] = $val;
			}
		}

		return $result;
	}

	/**
	 * Replace keys in array saving keys order.
	 * Recursive.
	 *
	 * @param mixed $array       Original array.
	 * @param array $replacement Array containing old keys as keys and new keys as values.
	 */
	public static function arrayReplaceKeys(mixed $array, array $replacement): array
	{
		if (is_array($array) && is_array($replacement)) {
			$newOrderArray = [];
			foreach ($array as $k => $v) {
				$key                 = array_key_exists($k, $replacement) ? $replacement[$k] : $k;
				$newOrderArray[$key] = is_array($v) ? self::arrayReplaceKeys($v, $replacement) : $v;
			}

			return $newOrderArray;
		}

		return $array;
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
