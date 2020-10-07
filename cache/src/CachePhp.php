<?php

namespace projectorangebox\cache;

use FS;
use projectorangebox\cache\CacheAbstract;
use projectorangebox\cache\CacheInterface;
use projectorangebox\cache\exceptions\CannotWrite;

class CachePhp extends CacheAbstract implements CacheInterface
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

		/* get absolute cache path */
		$this->cachePath = FS::resolve(rtrim($config['path'], '/')) . '/';

		$this->makeCacheFolder();
	}

	/**
	 * get
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function get(string $name)
	{
		$this->cleanName($name);

		$value = false;

		if ($cached = $this->read($name)) {
			if ($this->isExpired($cached['expire'])) {
				$this->delete($name);
			} else {
				$value = $cached['value'];
			}
		}

		return $value;
	}

	public function exists(string $name): bool
	{
		$this->cleanName($name);

		return file_exists($this->cachePath . $name . '.php');
	}

	/**
	 * save
	 *
	 * @param string $name
	 * @param mixed $value
	 * @param int $ttl
	 * @return bool
	 */
	public function save(string $name, $value, int $ttl = 60): bool
	{
		$originalName = $name;

		$this->cleanName($name);

		$namespace = dirname($name);

		$content = [
			'created' => time(),
			'ttl' => $ttl,
			'expire' => $this->createExpired($ttl),
			'name' => basename($name),
			'namespace' => $namespace,
			'originalName' => $originalName,
			'value' => $value,
		];

		$this->makeCacheFolder($namespace);

		return (bool)FS::atomic_file_put_contents($this->cachePath . $name . '.php', '<?php return ' . var_export($content, true) . ';');
	}

	protected function deleteSingle(string $name): bool
	{
		$this->cleanName($name);

		$file = $this->cachePath . $name . '.php';

		$unlinked = false;

		if (file_exists($file)) {
			$unlinked = unlink($file);
		}

		return $unlinked;
	}

	protected function getAllKeys(): array
	{
		$files = $this->rsearch($this->cachePath);

		$strlen = strlen($this->cachePath) - 1;

		/* now strip the root and extension out */
		foreach ($files as $index => $file) {
			$files[$index] = substr($file, $strlen, -4);
		}

		return $files;
	}

	/**
	 * cache_info
	 *
	 * @return array
	 */
	public function cacheInfo(): array
	{
		$records = [];

		foreach ($this->rsearch($this->cachePath) as $file) {
			$data = require $file;

			/* everything but the actual value */
			unset($data['value']);

			$records[] = $data;
		}

		return $records;
	}

	/**
	 * clean
	 *
	 * @return void
	 */
	public function clean(): bool
	{
		$this->rmdir($this->cachePath);

		$this->makeCacheFolder();

		return true;
	}

	protected function read(string $name)
	{
		$this->cleanName($name);

		$data = false;

		if ($this->exists($name)) {
			$data = require $this->cachePath . $name . '.php';
		}

		return $data;
	}

	protected function makeCacheFolder(string $folder = ''): void
	{
		$folder = rtrim($folder, '/');

		/* let's make sure the compile folder is there before we try to save the compiled file! */
		if (!file_exists($this->cachePath . $folder)) {
			mkdir($this->cachePath . $folder, 0777, true);
		}

		/* is the folder writable by us? */
		if (!is_writable($this->cachePath . $folder)) {
			throw new CannotWrite($this->cachePath . $folder);
		}
	}

	protected function rsearch(string $folder): array
	{
		$dir = new \RecursiveDirectoryIterator($folder);
		$ite = new \RecursiveIteratorIterator($dir);
		$files = new \RegexIterator($ite, '/^.+\.php$/i', \RegexIterator::GET_MATCH);

		$fileList = [];

		foreach ($files as $file) {
			$fileList = array_merge($fileList, $file);
		}

		return $fileList;
	}

	protected function rmdir(string $folder): bool
	{
		$dir = new \RecursiveDirectoryIterator($folder, \RecursiveDirectoryIterator::SKIP_DOTS);
		$ite = new \RecursiveIteratorIterator($dir, \RecursiveIteratorIterator::CHILD_FIRST);

		foreach ($ite as $fileinfo) {
			if ($fileinfo->isDir()) {
				$this->rmdir($fileinfo->getRealPath());
			} else {
				unlink($fileinfo->getRealPath());
			}
		}

		return \rmdir($folder);
	}
} /* end class */
