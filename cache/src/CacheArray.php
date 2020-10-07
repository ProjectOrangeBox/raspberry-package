<?php

namespace projectorangebox\cache;

class CacheArray extends CacheAbstract implements CacheInterface
{
	protected $cache = [];

	/**
	 * get
	 *
	 * @param string $key
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

	protected function read($name)
	{
		$this->cleanName($name);

		return ($this->exists($name)) ? $this->cache[$name] : false;
	}

	public function exists(string $name): bool
	{
		$this->cleanName($name);

		return \array_key_exists($name, $this->cache);
	}

	/**
	 * save
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param int $ttl
	 * @return bool
	 */
	public function save(string $name, $value, int $ttl = 60): bool
	{
		$originalName = $name;

		$this->cleanName($name);

		$this->cache[$name] = [
			'created' => time(),
			'ttl' => $ttl,
			'expire' => $this->createExpired($ttl),
			'name' => basename($name),
			'namespace' => dirname($name),
			'originalName' => $originalName,
			'value' => $value,
		];

		return true;
	}

	protected function deleteSingle(string $name): bool
	{
		$this->cleanName($name);

		unset($this->cache[$name]);

		return true;
	}

	protected function getAllKeys(): array
	{
		return \array_keys($this->cache);
	}

	/**
	 * clean
	 *
	 * @return void
	 */
	public function clean(): bool
	{
		$this->cache = [];

		return true;
	}
} /* end class */
