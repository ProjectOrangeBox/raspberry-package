<?php

/**
 * array_get_by
 * Get an array value by notation
 *
 * @param mixed[] $array
 * @param string $notation
 * @param mixed $default
 * @return mixed
 */
function array_get_by(array $array, string $notation, $default = null) /* mixed */
{
	$value = $default;

	if (is_array($array) && array_key_exists($notation, $array)) {
		/* single level notation */
		$value = $array[$notation];
	} else {
		/* multiple notations */
		$segments = explode('.', $notation);

		foreach ($segments as $segment) {
			if (is_array($array) && array_key_exists($segment, $array)) {
				$value = $array = $array[$segment];
			} else {
				$value = $default;
				break;
			}
		}
	}

	return $value;
}

/**
 * array_set_by
 * Set an array value by notation
 *
 * @param array $array
 * @param string $notation
 * @param mixed $value
 * @return void
 */
function array_set_by(array &$array, string $notation, $value): void
{
	$keys = explode('.', $notation);

	while (count($keys) > 1) {
		$key = array_shift($keys);

		if (!isset($array[$key])) {
			$array[$key] = [];
		}

		$array = &$array[$key];
	}

	$key = reset($keys);

	$array[$key] = $value;
}

/**
 * array_sort_by_column
 * Sort an array by a column in that array
 *
 * @param array $array array to sort passed by reference
 * @param string $column column to sort by
 * @param int $dir Either SORT_ASC to sort ascendingly or SORT_DESC to sort descendingly.
 * @param int $flags Sort options for the previous array argument see PHP array_multisort
 * @return void
 */
function array_sort_by_column(array &$array, string $column, int $dir = SORT_ASC, int $flags = null)
{
	$sortColumn = array_column($array, $column);

	array_multisort($sortColumn, $dir, $array, $flags);
}

/**
 * return a new arrayfrom the data in the array
 * where the the keys are remapped from map array to the values in map array.
 *
 * @param array $array
 * @param array $mapArray
 * @return array
 */
function array_remap(array $array, array $mapArray): array
{
	$newArray = [];

	foreach ($mapArray as $current => $new) {
		$newArray[$new] = $array[$current];
	}

	return $newArray;
}
