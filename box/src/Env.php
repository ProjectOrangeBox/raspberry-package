<?php

if (!function_exists('env')) {
	/**
	 * env
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	function env(string $key, $default = null)
	{
		return isset($_ENV[$key]) ? $_ENV[$key] : $default;
	}
}
