<?php

namespace projectorangebox\pear;

use FS;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Collector
{
	static public function collect(array $config): array
	{
		$found = [];

		$pearsFolder = '/' . trim(($config['plugin folder'] ?? '/pears/'), '/') . '/';
		$pearExtension = '.' . trim(($config['plugin extension'] ?? 'php'), '.');

		$regex = '%^(?<folder>.*)' . str_replace('/', '\/', $pearsFolder) . 'Pear(?<filename>.*)' . $pearExtension . '$%m';

		/* find all matching starting at __ROOT__ */
		$allFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(FS::resolve('')), RecursiveIteratorIterator::SELF_FIRST);

		foreach ($allFiles as $filename) {
			if (preg_match($regex, $filename, $matches, PREG_OFFSET_CAPTURE, 0)) {
				$found[self::extractClassName($filename)] = self::extractNameSpace($filename);
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
