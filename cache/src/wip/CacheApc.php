<?php

namespace projectorangebox\cache;

use function apc_cache_info;
use function apc_clear_cache;
use function apc_delete;
use function apc_exists;
use function apc_fetch;
use function apc_sma_info;
use function apc_store;

use projectorangebox\cache\CacheAbstract;
use projectorangebox\cache\CacheInterface;

class CacheApc extends CacheAbstract implements CacheInterface
{
	public function get(string $name)
	{
		$this->cleanName($name)

		return apc_fetch($name);
	}

	public function exists(string $key): bool
	{
		$this->cleanName($name)

		return apc_exists($name);
	}

	public function save(string $key, $value, int $ttl = 60): bool
	{
		$this->cleanName($name)

		return apc_store($name, $value, $ttl);
	}

	protected function deleteSingle(string $name): bool
	{
		$this->cleanName($name);

		return apc_delete($key) || !apc_exists($key);
	}

	protected function deleteNamespace(string $namespace): bool
	{
		$namespace = substr($namespace, 0, -2);

		$this->cleanName($namespace);

		foreach (array_keys($this->cache) as $name) {
			if (substr($name, 0, strlen($namespace) == $namespace)) {
				unset($this->cache[$name]);
			}
		}

		return true;
	}

	public function cacheInfo(): array
	{
		$info = apc_cache_info('', true);
		$sma  = apc_sma_info();

		return [
			'hits' => $info['num_hits'],
			'misses' => $info['num_misses'],
			'uptime' => $info['start_time'],
			'memory_usage' => $info['mem_size'],
			'memory_available' => $sma['avail_mem'],
		];
	}

	public function clean(): bool
	{
		return apc_clear_cache() && apc_clear_cache('user');
	}
} /* end class */
