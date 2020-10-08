<?php

namespace projectorangebox\views;

use FS;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Collector
{
	static public function collect(array $config): array
	{
		$found = [];

		$viewsFolder = '/' . trim(($config['view folder'] ?? '/views/'), '/') . '/';
		$viewExtension = '.' . trim(($config['view extension'] ?? 'php'), '.');

		$regex = '%^(?<folder>.*)' . str_replace('/', '\/', $viewsFolder) . '(?<filename>.*)' . $viewExtension . '$%m';

		/* find all matching starting at __ROOT__ */
		$allFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(FS::resolve('')), RecursiveIteratorIterator::SELF_FIRST);

		foreach ($allFiles as $filename) {
			if (preg_match($regex, $filename, $matches, PREG_OFFSET_CAPTURE, 0)) {
				$found[trim(strtolower($matches['filename'][0]), '/')] = $viewsFolder . $matches['filename'][0] . $viewExtension;
			}
		}

		return $found;
	}
} /* end class */
