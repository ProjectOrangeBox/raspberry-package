<?php

if (!function_exists('mergeWithEnv')) {
	function mergeWithEnv(array $array): void
	{
		$_ENV = array_replace($_ENV, $array);
	}
}

if (!function_exists('mergeDotEnv')) {
	function mergeDotEnv(string $envFile = null): void
	{
		$envFile = ($envFile) ?? '.env';

		$envFile = __ROOT__ . '/' . trim($envFile, '/');

		if (!file_exists($envFile)) {
			throw new Exception('.env File Not Found.');
		}

		$envArray = parse_ini_file($envFile, true, INI_SCANNER_TYPED);

		if (is_array($envArray)) {
			mergeWithEnv($envArray);
		}
	}
}
