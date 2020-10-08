<?php

namespace projectorangebox\handlebars;

use FS;
use projectorangebox\cache\CacheInterface;
use projectorangebox\cache\exceptions\CannotWrite;

class HandlebarsCache implements CacheInterface
{
	protected $config = [];
	protected $cachePath = '';
	protected $cachePrefix = 'hbc.';

	public function __construct(array $config)
	{
		$this->config = $config;

		$this->cachePrefix = $config['cache prefix'] ?? $this->cachePrefix;

		$cachePath = $this->config['cache folder'] ?? '/var';

		/* we must have a working directory which is read and write */
		$this->cachePath = $this->makeCacheFolder($cachePath);
	}

	public function get(string $key)
	{
		$cacheFile = $this->getCachePath($key);

		$value = false;

		if (FS::file_exists($cacheFile)) {
			$cacheFileAbs = FS::resolve($cacheFile);

			$value = include $cacheFileAbs;
		}

		return $value;
	}

	public function exists(string $key): bool
	{
		$cacheFile = $this->getCachePath($key);

		return $this->FS::file_exists($cacheFile);
	}

	public function save(string $key, $value, int $ttl = 60): bool
	{
		$cacheFile = $this->getCachePath($key);

		$bytesWritten = FS::file_put_contents($cacheFile, $value);

		FS::chmod($cacheFile, 0666);

		return ($bytesWritten > 0);
	}

	public function delete(string $key): bool
	{
		$cacheFile = $this->getCachePath($key);

		if (FS::file_exists($cacheFile)) {
			FS::unlink($cacheFile);
		}

		return true;
	}

	public function cacheInfo(): array
	{
		$cache = [];

		foreach (FS::glob($this->cachePath . $this->cachePrefix . '*') as $path) {
			$cache[] = [
				'fullname' => $path,
				'key' => substr(basename($path, '.php'), strlen($this->cachePrefix)),
				'stat' => FS::stat($path),
			];
		}

		return $cache;
	}

	public function clean(): bool
	{
		foreach (FS::glob($this->cachePath . $this->cachePrefix . '*') as $path) {
			if (FS::file_exists($path)) {
				FS::unlink($path);
			}
		}

		return true;
	}

	protected function getCachePath(string $key): string
	{
		return $this->cachePath . $this->cachePrefix . preg_replace('@[^A-Za-z0-9]@', '-', $key) . '.php';
	}

	protected function makeCacheFolder(string $folder): string
	{
		$folder = trim($folder, '/');

		/* let's make sure the compile folder is there before we try to save the compiled file! */
		if (!FS::file_exists($folder)) {
			FS::mkdir($folder, 0755, true);
		}

		/* is the folder writable by us? */
		if (!FS::is_writable($folder)) {
			throw new CannotWrite($folder);
		}

		return $folder . '/';
	}
} /* end class */
