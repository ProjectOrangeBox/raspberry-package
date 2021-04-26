<?php

namespace projectorangebox\log\handlers;

use FS;
use projectorangebox\log\LoggerAbstract;
use projectorangebox\log\LoggerInterface;
use projectorangebox\log\exceptions\NotWritable;

class File extends LoggerAbstract implements LoggerInterface
{
	protected $logFile = null;
	protected $filePermissions = 666; /* converted to octdec before chmod called */

	public function __construct(array $config)
	{
		parent::__construct(array_replace(['threshold' => 0, 'path' => '/var/logs', 'cleanup' => 7], $config));

		if ($this->enabled) {
			/* absolute log folder */
			$absoluteLogFolder = FS::resolve($this->config['path'] ?? '/');

			/* can we write to this folder? */
			if (!FS::is_dir($absoluteLogFolder) || !FS::is_writable($absoluteLogFolder)) {
				$this->enabled = false;

				throw new NotWritable(FS::resolve($absoluteLogFolder, true));
			}

			$this->filePermissions = $this->config['permissions'] ?? $this->filePermissions;

			/* file name with extension and date replacement "string" */
			$logName = $this->config['filename'] ?? 'log-%d.log';

			/* file date format */
			$dateFmt = $this->config['filename date format'] ?? 'Y-m-d';

			$this->logFile = $absoluteLogFolder . '/' . str_replace('%d', date($dateFmt), $logName);

			/* run our built in clean up method */
			if (isset($this->config['cleanup']) && $this->config['cleanup']) {
				/* only on 10% of the requests try to clean up */
				if (\mt_rand(0, 100) > 90) {
					foreach (glob($absoluteLogFolder . '/' . str_replace('%d', '*', $logName)) as $file) {
						if (time() - filemtime($file) >= (86400 * (int) $this->config['cleanup'])) {
							unlink($file);
						}
					}
				}
			}
		}
	} /* end construct */

	public function log(string $level, string $message, array $context = []): bool
	{
		$bytes = 0;

		if ($this->enabled) {
			if ($this->testLevel($level)) {
				$bytes = FS::file_put_contents($this->logFile, $this->format($level, $message, $context), FILE_APPEND | LOCK_EX);

				FS::chmod($this->logFile, octdec($this->filePermissions));
			}
		}

		return ($bytes > 0);
	}

	public function getLogFile(): string
	{
		return $this->logFile;
	}
} /* End of Class */
