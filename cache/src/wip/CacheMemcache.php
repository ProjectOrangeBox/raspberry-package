<?php

namespace projectorangebox\cache;

use Exception;
use projectorangebox\cache\CacheAbstract;
use projectorangebox\cache\CacheInterface;
use projectorangebox\log\LoggerTrait;

class CacheMemcached extends CacheAbstract implements CacheInterface
{
	use LoggerTrait;

	protected $config = [];

	protected $memcached;
	protected $ttl;

	/**
	 * __construct
	 *
	 * @param array[] $config configuration array
	 */
	public function __construct(array $config)
	{
		$this->config = $config;

		$this->ttl = $config['ttl'];

		if (class_exists('Memcached', FALSE)) {
			$this->memcached = new \Memcached();
		} elseif (class_exists('Memcache', FALSE)) {
			$this->memcached = new \Memcache();
		} else {
			throw new Exception('Cache: Failed to create Memcache(d) object; extension not loaded?');
		}

		foreach ($this->config['memcache servers'] as $cacheServer) {
			if ($this->memcached instanceof \Memcache) {
				/* Third parameter is persistence and defaults to TRUE. */
				$this->memcached->addServer($cacheServer['hostname'], $cacheServer['port'], TRUE, $cacheServer['weight']);
			} elseif ($this->memcached instanceof \Memcached) {
				$this->memcached->addServer($cacheServer['hostname'], $cacheServer['port'], $cacheServer['weight']);
			}
		}

		$this->log('info', 'CacheMemcached::__construct');
	}

	/**
	 * get
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get(string $key)
	{
		$data = $this->memcached->get($key);

		return is_array($data) ? $data[0] : $data;
	}

	public function exists(string $name): bool
	{
		$this->cleanName($name);

		$data = $this->get($name);

		return ($data !== false);
	}

	/**
	 * getMetadata
	 *
	 * @param string $key
	 * @return array
	 */
	public function getMetadata(string $key): array
	{
		$stored = $this->memcached->get($key);

		if (count($stored) !== 3) {
			return FALSE;
		}

		list($data, $time, $ttl) = $stored;

		return array(
			'expire'	=> $time + $ttl,
			'mtime'		=> $time,
			'data'		=> $data
		);
	}

	/**
	 * save
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param int $ttl
	 * @return bool
	 */
	public function save(string $key, $value, int $ttl = null, bool $raw = false): bool
	{
		$ttl = $ttl ?? $this->ttl;

		if ($raw !== true) {
			$value = array($value, time(), $ttl);
		}

		if ($this->memcached instanceof \Memcached) {
			return $this->memcached->set($key, $value, $ttl);
		} elseif ($this->memcached instanceof \Memcache) {
			return $this->memcached->set($key, $value, 0, $ttl);
		}

		return false;
	}

	/**
	 * delete
	 *
	 * @param string $key
	 * @return void
	 */
	public function delete(string $key): void
	{
		$this->memcached->delete($key);
	}

	/**
	 * cache_info
	 *
	 * @return array
	 */
	public function cacheInfo(): array
	{
		return $this->memcached->getStats();
	}

	/**
	 * clean
	 *
	 * @return void
	 */
	public function clean(): void
	{
		$this->memcached->flush();
	}

	public function __destruct()
	{
		if ($this->memcached instanceof \Memcache) {
			$this->memcached->close();
		} elseif ($this->memcached instanceof \Memcached && method_exists($this->memcached, 'quit')) {
			$this->memcached->quit();
		}
	}
} /* end class */
