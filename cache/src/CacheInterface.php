<?php

namespace projectorangebox\cache;

interface CacheInterface
{
	/**
	 * __construct
	 *
	 * @param string[] $config
	 * @return void
	 */
	public function __construct(array $config);

	/**
	 * get
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function get(string $name); /* mixed */

	/**
	 * cache key exists
	 *
	 * @param string $name
	 * @return bool
	 */
	public function exists(string $name): bool;

	/**
	 * save
	 *
	 * @param string $name
	 * @param mixed $value
	 * @param int $ttl
	 * @return void
	 */
	public function save(string $name, $value, int $ttl = 60): bool;

	/**
	 * delete
	 *
	 * @param string $name
	 * @return bool
	 */
	public function delete(string $name): bool;

	/**
	 * remove all
	 *
	 * @return bool
	 */
	public function clean(): bool;

	/**
	 * get information about all cached items except the actual data
	 *
	 * @return array
	 */
	public function cacheInfo(): array;
}
