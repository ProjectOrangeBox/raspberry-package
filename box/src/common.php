<?php
/*

Any of these functions and/or classes
can be overrirden by loading them before calling composers autoloader

before -> require __ROOT__ . '/vendor/autoload.php';

*/

/* stateless (and therefore no need to override) */
require __DIR__ . '/Array.php';

/* provide some global functions */
require __DIR__ . '/Env.php';
require __DIR__ . '/Merge.php';

/* provide some static classes */

/* have they already loaded a path class? */
if (!class_exists('Path', false)) {
	require __DIR__ . '/Path.php';
}

/* have they already loaded a send class? */
if (!class_exists('Send', false)) {
	require __DIR__ . '/Send.php';
}

/**
 * Try to convert a value to it's real type
 * this is nice for pulling string from a database
 * such as configuration values stored in string format
 *
 * @param string $value
 *
 * @return mixed
 *
 */
if (!function_exists('convert_to_real')) {
	function convert_to_real(string $value)
	{
		/* return on first match multiple exists */
		switch (trim(strtolower($value))) {
			case 'true':
				return true;
				break;
			case 'false':
				return false;
				break;
			case 'empty':
				return '';
				break;
			case 'null':
				return null;
				break;
			default:
				if (is_numeric($value)) {
					return (is_float($value)) ? (float)$value : (int)$value;
				}
		}

		$json = @json_decode($value, true);

		return ($json !== null) ? $json : $value;
	}
}

/**
 * Try to convert a value back into a string
 * this is nice for storing string into a database
 * such as configuration values stored in string format
 *
 * @param mixed $value
 *
 * @return string
 *
 */
if (!function_exists('convert_to_string')) {
	function convert_to_string($value): string
	{
		/* return on first match multiple exists */

		if (is_array($value)) {
			return str_replace('stdClass::__set_state', '(object)', var_export($value, true));
		}

		if ($value === true) {
			return 'true';
		}

		if ($value === false) {
			return 'false';
		}

		if ($value === null) {
			return 'null';
		}

		return (string) $value;
	}
}
