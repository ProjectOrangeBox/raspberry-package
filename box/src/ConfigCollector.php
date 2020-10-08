<?php

namespace projectorangebox\box;

use FS;
use Exception;
use Brick\VarExporter\VarExporter;
use Brick\VarExporter\ExportException;
use projectorangebox\router\Formatter;
use projectorangebox\pear\Collector as pearCollector;
use projectorangebox\views\Collector as viewsCollector;

class ConfigCollector
{
	/**
	 * @param string $configFolder
	 * @return array
	 * @throws Exception
	 */
	static public function collected(string $configFolder): array
	{
		return self::getConfigFiles($configFolder);
	}

	/**
	 * @param string $configFolder
	 * @param bool $wrap
	 * @return string
	 * @throws Exception
	 * @throws ExportException
	 */
	static public function source(string $configFolder, bool $wrap = false): string
	{
		$source = VarExporter::export(self::getConfigFiles($configFolder));

		return ($wrap) ? '<?php' . PHP_EOL . 'return ' . $source . ';' . PHP_EOL : $source;
	}

	/**
	 * @param string $configFolder
	 * @param string $phpFile
	 * @return void
	 * @throws Exception
	 * @throws ExportException
	 */
	static public function generateFile(string $configFolder, string $phpFile): bool
	{
		$bytesWritten = FS::file_put_contents($phpFile, self::source($configFolder, true));

		FS::chmod($phpFile, 0666);

		return ($bytesWritten > 0);
	}

	/**
	 * @param string $configFolder
	 * @return void
	 * @throws Exception
	 */
	static protected function getConfigFiles(string $configFolder): array
	{
		$configs = [];

		FS::setRoot(__ROOT__, true);

		if (!FS::is_dir($configFolder)) {
			die('Config folder "' . $configFolder . '" not found.');
		}

		$configFiles = FS::glob($configFolder . '/*.php');

		foreach ($configFiles as $configFile) {
			$configs[basename($configFile, '.php')] = require(FS::resolve($configFile));
		}

		/* find and insert all views and plugins */
		$configs['views']['views'] = viewsCollector::collect($configs['views']);
		$configs['pear']['plugins'] = pearCollector::collect($configs['pear']);

		/**
		 * format the routes and middleware routes
		 * because the class exspects them to be in a
		 * different format than the human readable format
		 */
		$configs['router']['routes'] = Formatter::format($configs['router']);
		$configs['router']['routes format'] = 'raw';

		$configs['middleware']['request'] = Formatter::format($configs['middleware'] + ['routes' => $configs['middleware']['request']]);
		$configs['middleware']['request format'] = 'raw';

		$configs['middleware']['response'] = Formatter::format($configs['middleware'] + ['routes' => $configs['middleware']['response']]);
		$configs['middleware']['response format'] = 'raw';

		return $configs;
	}
} /* end class */
