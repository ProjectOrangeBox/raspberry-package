<?php

namespace projectorangebox\cache;

use function apcu_cache_info;
use function apcu_clear_cache;
use function apcu_delete;
use function apcu_exists;
use function apcu_fetch;
use function apcu_sma_info;
use function apcu_store;

use projectorangebox\cache\CacheAbstract;
use projectorangebox\cache\CacheInterface;

class CacheApc extends CacheAbstract  implements CacheInterface
{
	public function get(string $key)
	{
		return apcu_fetch($id);
	}

	public function exists(string $key): bool
	{
		return apcu_exists($key);
	}

	public function save(string $key, $value, int $ttl = 60): bool
	{
		return apcu_store($key, $value, $ttl);
	}

	public function delete(string $key): bool
	{
		return apcu_delete($key) || !apcu_exists($key);
	}

	public function cacheInfo(): array
	{
		$info = apcu_cache_info('', true);
		$sma  = apcu_sma_info();

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
		return apcu_clear_cache();
	}
} /* end class */
