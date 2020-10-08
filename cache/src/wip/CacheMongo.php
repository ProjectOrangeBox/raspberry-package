<?php

namespace projectorangebox\cache;

use projectorangebox\cache\CacheAbstract;
use projectorangebox\cache\CacheInterface;

class CacheMongo extends CacheAbstract implements CacheInterface
{
	public function __construct(array $config)
	{
	}

	public function get(string $key)
	{
	}

	public function exists(string $key): bool
	{
	}

	public function save(string $key, $value, int $ttl = 60): bool
	{
	}

	public function delete(string $key): bool
	{
	}

	public function cacheInfo(): array
	{
	}

	public function clean(): bool
	{
	}
} /* end class */
