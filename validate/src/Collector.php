<?php

namespace projectorangebox\validate;

use FS;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Collector
{
	static public function collect(array $config): array
	{
		$folder = '/' . trim(($config['rules folder'] ?? '/rules/'), '/') . '/';
		$extension = '.' . trim(($config['rules extension'] ?? 'php'), '.');

		$config['rules'] = self::_collect($folder, $extension);

		$folder = '/' . trim(($config['filters folder'] ?? '/filters/'), '/') . '/';
		$extension = '.' . trim(($config['filters extension'] ?? 'php'), '.');

		$config['filters'] = self::_collect($folder, $extension);

		return $config;
	}

	static public function _collect(string $folder, string $extension): array
	{
		$found = [];

		$regex = '%^(?<folder>.*)' . str_replace('/', '\/', $folder) . '(?<filename>.*)' . $extension . '$%m';

		/* find all matching starting at __ROOT__ */
		$allFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(FS::resolve('')), RecursiveIteratorIterator::SELF_FIRST);

		foreach ($allFiles as $filename) {
			if (preg_match($regex, $filename, $matches, PREG_OFFSET_CAPTURE, 0)) {
				$found[strtolower(self::extractClassName($filename))] = self::extractNameSpace($filename);
			}
		}

		return $found;
	}

	static protected function extractNameSpace(string $filepath): string
	{
		$contents = php_strip_whitespace(FS::resolve($filepath));

		// namespace projectorangebox\validation\rules;

		return '\\' . trim(self::between('namespace ', ';', $contents)) . '\\' . trim(self::between('class ', ' extends ', $contents));
	}

	static protected function extractClassName(string $filepath): string
	{
		$contents = php_strip_whitespace(FS::resolve($filepath));

		// namespace projectorangebox\validation\rules;

		return trim(self::between('class ', ' extends ', $contents));
	}

	static protected function between($start, $end, $string)
	{
		$match = '';

		if (preg_match('%^(.*)' . preg_quote($start) . '(?<match>.*?)' . preg_quote($end) . '(.*)$%m', $string, $matches, PREG_OFFSET_CAPTURE, 0)) {
			$match = trim($matches['match'][0]);
		}

		return $match;
	}
}/* end class */
