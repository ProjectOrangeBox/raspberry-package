<?php

namespace projectorangebox\cache;

use FS;
use projectorangebox\cache\CacheAbstract;
use projectorangebox\cache\CacheInterface;
use projectorangebox\cache\exceptions\CannotWrite;

class CacheFile extends CacheAbstract implements CacheInterface
{
	protected $cachePath = '';

	/**
	 * __construct
	 *
	 * @param string[] $config configuration array
	 */
	public function __construct(array $config)
	{
		parent::__construct($config);

		/* make cache path ready to use */
		$this->cachePath = rtrim($config['path'], '/') . '/';

		$this->makeCacheFolder($this->cachePath);
	}

	protected function makeCacheFolder(string $folder): void
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
	}

	/**
	 * get
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get(string $key)
	{
		$this->cleanKey($key);

		$get = false;

		if ($this->ttl > 0) {
			if (FS::file_exists($this->cachePath . $key . '.meta.php') && FS::file_exists($this->cachePath . $key . '.php')) {
				$meta = $this->getMetadata($key);

				if ($this->isExpired($meta['expire'])) {
					$this->delete($key);
				} else {
					$get = include FS::resolve($this->cachePath . $key . '.php');
				}
			}
		}

		return $get;
	}

	public function exists(string $key): bool
	{
		$this->cleanKey($key);

		$file = $this->cachePath . $key;

		return (FS::is_file($file . '.meta.php') && FS::is_file($file . '.php'));
	}

	/**
	 * save
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param int $ttl
	 * @return bool
	 */
	public function save(string $key, $value, int $ttl = 60): bool
	{
		$this->cleanKey($key);

		$valuePHP = FS::var_export_php($value);
		$metaPHP = FS::var_export_php($this->buildMetadata($valuePHP, $this->ttl($ttl)));
		$metaBytesWritten = FS::atomic_file_put_contents($this->cachePath . $key . '.meta.php', $metaPHP);
		$valueBytesWritten = FS::atomic_file_put_contents($this->cachePath . $key . '.php', $valuePHP);

		return ((bool) $metaBytesWritten  && (bool) $valueBytesWritten);
	}

	/**
	 * delete
	 *
	 * @param string $key
	 * @return void
	 */
	public function delete(string $key): bool
	{
		$this->cleanKey($key);

		$file = $this->cachePath . $key . '.php';

		if (FS::file_exists($file)) {
			FS::unlink($file);
		}

		return true;
	}

	/**
	 * cache_info
	 *
	 * @return array
	 */
	public function cacheInfo(): array
	{
		$keys = [];

		foreach (FS::glob($this->cachePath . '*') as $path) {
			$key = FS::basename($path);

			$keys[$key] = $this->getMetadata($key);
		}

		return $keys;
	}

	/**
	 * clean
	 *
	 * @return void
	 */
	public function clean(): bool
	{
		foreach (FS::glob($this->cachePath . '*') as $path) {
			$this->delete($path);
		}

		return true;
	}

	/**
	 * buildMetadata
	 *
	 * @param string $valueString
	 * @param int $ttl
	 * @return array
	 */
	protected function buildMetadata(string $valueString, int $ttl): array
	{
		return [
			'strlen' => strlen($valueString),
			'time' => time(),
			'ttl' => (int) $ttl,
			'expire' => (time() + $ttl)
		];
	}

	/**
	 * getMetadata
	 *
	 * @param string $key
	 * @return array
	 */
	protected function getMetadata(string $key): array
	{
		$this->cleanKey($key);

		$file = $this->cachePath . $key;

		$metaData = [];

		if (FS::is_file($file . '.meta.php') && FS::is_file($file . '.php')) {
			$metaData = include FS::resolve($file . '.meta.php');
		}

		return $metaData;
	}
} /* end class */
