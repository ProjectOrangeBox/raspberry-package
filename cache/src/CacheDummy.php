<?php

namespace projectorangebox\cache;

use projectorangebox\cache\CacheAbstract;
use projectorangebox\cache\CacheInterface;

class CacheDummy extends CacheAbstract implements CacheInterface
{
	/**
	 * get
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get(string $key)
	{
		return false;
	}

	/**
	 * key exists
	 *
	 * @param string $key
	 * @return bool
	 */
	public function exists(string $key): bool
	{
		return false;
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
		return true;
	}

	/**
	 * delete
	 *
	 * @param string $key
	 * @return void
	 */
	public function delete(string $key): bool
	{
		return true;
	}

	/**
	 * cache_info
	 *
	 * @return array
	 */
	public function cacheInfo(): array
	{
		return [];
	}

	/**
	 * clean
	 *
	 * @return void
	 */
	public function clean(): bool
	{
		return true;
	}
} /* end class */
