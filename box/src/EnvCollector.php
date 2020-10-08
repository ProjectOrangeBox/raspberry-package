<?php

namespace projectorangebox\box;

use FS;
use Exception;
use Brick\VarExporter\VarExporter;
use Brick\VarExporter\ExportException;

class EnvCollector
{
	/**
	 * @param string $envFile
	 * @return array
	 * @throws Exception
	 */
	static public function collected(string $envFile): array
	{
		FS::setRoot(__ROOT__, true);

		if (!FS::file_exists($envFile)) {
			die('Env File ' . $envFile . ' Not Found.');
		}

		$envFile = FS::resolve($envFile, false);

		$envArray = parse_ini_file($envFile, true, INI_SCANNER_TYPED);

		return array_replace($_ENV, $envArray);
	}

	/**
	 * @param string $envFile
	 * @param bool $wrap
	 * @return string
	 * @throws Exception
	 * @throws ExportException
	 */
	static public function source(string $envFile, bool $wrap = false): string
	{
		$envFile = ($envFile) ?? '.env';

		$source = VarExporter::export(self::collected($envFile));

		return ($wrap) ? '<?php' . PHP_EOL . 'return ' . $source . ';' . PHP_EOL : $source;
	}

	/**
	 * @param string $envFile
	 * @param string $phpFile
	 * @return bool
	 * @throws Exception
	 * @throws ExportException
	 */
	static public function generateFile(string $envFile, string $phpFile): bool
	{
		$bytesWritten = FS::file_put_contents($phpFile, self::source($envFile, true));

		FS::chmod($phpFile, 0666);

		return ($bytesWritten > 0);
	}
} /* end class */
