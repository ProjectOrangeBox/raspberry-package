<?php

namespace projectorangebox\cache;

use Redis;
use projectorangebox\cache\CacheAbstract;
use projectorangebox\cache\CacheException;
use projectorangebox\cache\CacheInterface;

class CacheRedis extends CacheAbstract implements CacheInterface
{
	protected $redis = null;

	public function __construct(array $config)
	{
		parent::__construct($config);

		if (!\array_key_exists('redis', $config)) {
			throw new CacheException('Could not find redis config.');
		}

		$redis = new Redis();

		$redisConfig = $config['redis'];

		$persistent = ($redisConfig['persistent']) ?? true;

		$server = ($redisConfig['server']) ?? '127.0.0.1';
		$port = ($redisConfig['port']) ?? 6379;
		$timeout = ($redisConfig['timeout']) ?? 1;
		$persistentId = ($redisConfig['timeout']) ?? 'x';
		$reserved = ($redisConfig['reserved']) ?? null;
		$retryInterval = ($redisConfig['retry interval']) ?? 100;
		$readTimeout = ($redisConfig['read timeout']) ?? 0;

		if ($persistent) {
			$redis->pconnect($server, $port, $timeout, $persistentId, $retryInterval, $readTimeout); // 1 sec timeout, 100ms delay between reconnection attempts.
		} else {
			$redis->connect($server, $port, $timeout, $reserved, $retryInterval, $readTimeout); // 1 sec timeout, 100ms delay between reconnection attempts.
		}

		$redis->setOption(Redis::OPT_SERIALIZER, $this->getSerializerValue());

		$username = ($redisConfig['username']) ?? null;
		$password = ($redisConfig['password']) ?? null;

		if ($username !== null && $password !== null) {
			$redis->auth([$username, $password]);
		} elseif ($password !== null) {
			$redis->auth($password);
		}

		$this->redis = $redis;
	}

	public function get(string $key)
	{
		return $this->redis->get($key);
	}

	public function exists(string $key): bool
	{
		$exists = $this->redis->exists($key);

		return (is_bool($exists)) ? $exists : ($exists > 0);
	}

	public function save(string $key, $value, int $ttl = 60): bool
	{
		if ($ttl > 0) {
			return $this->redis->setex($key, $ttl, $value);
		}

		return $this->redis->set($key, $value);
	}

	protected function getAllKeys(): array
	{
		return $this->redis->keys('*');
	}

	public function deleteSingle(string $key): bool
	{
		return ($this->redis->del($key) >= 0);
	}

	public function cacheInfo(): array
	{
		$info = $this->redis->info();

		return [
			'hits' => $info['keyspace_hits'],
			'misses' => $info['keyspace_misses'],
			'uptime' => $info['uptime_in_seconds'],
			'memory usage' => $info['used_memory'],
			'memory available' => false,
			'keys' => $this->getAllKeys(),
		];
	}

	public function clean(): bool
	{
		return $this->redis->flushDB();
	}

	/**
	 * Returns the serializer constant to use. If Redis is compiled with
	 * igbinary support, that is used. Otherwise the default PHP serializer is
	 * used.
	 *
	 * @return int One of the Redis::SERIALIZER_* constants
	 */
	protected function getSerializerValue()
	{
		return (defined('Redis::SERIALIZER_IGBINARY') && extension_loaded('igbinary')) ? Redis::SERIALIZER_IGBINARY : Redis::SERIALIZER_PHP;
	}
} /* end class */
