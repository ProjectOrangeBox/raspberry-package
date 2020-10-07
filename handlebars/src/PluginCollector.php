<?php

namespace projectorangebox\handlebars;

use FS;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class PluginCollector
{
	static public function collect(array $config): array
	{
		$found = [];

		$pluginsFolder = '/' . trim(($config['plugin folder'] ?? '/plugins/'), '/') . '/';
		$pluginExtension = '.' . trim(($config['plugin extension'] ?? 'php'), '.');

		$regex = '%^(?<folder>.*)' . str_replace('/', '\/', $pluginsFolder) . '(?<filename>.*)' . $pluginExtension . '$%m';

		/* find all matching starting at __ROOT__ */
		$allFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(FS::resolve('')), RecursiveIteratorIterator::SELF_FIRST);

		foreach ($allFiles as $filename) {
			if (preg_match($regex, $filename, $matches, PREG_OFFSET_CAPTURE, 0)) {
				$found[trim(strtolower($matches['filename'][0]), '/')] = FS::resolve($matches[0][0], true);
			}
		}

		return $found;
	}
}
