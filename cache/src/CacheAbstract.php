<?php

namespace projectorangebox\cache;

use projectorangebox\cache\CacheInterface;

abstract class CacheAbstract
{
	protected $config = [];

	public function __construct(array $config)
	{
		$this->config = $config;
	}

	/**
	 * delete
	 *
	 * @param string $name
	 * @return void
	 */
	public function delete(string $name): bool
	{
		$bool = true;

		/* entire namespace or just a single record? */
		if (substr($name, -2) != '/*') {
			$bool = $this->deleteSingle($name);
		} else {
			$name = substr($name, 0, -2);
			$strlen = strlen($name);

			foreach ($this->getAllKeys() as $key) {
				if (substr($key, 0, $strlen) == $name) {
					$this->deleteSingle($key);
				}
			}
		}

		return $bool;
	}

	/**
	 * cache_info
	 *
	 * @return array
	 */
	public function cacheInfo(): array
	{
		$records = [];

		foreach ($this->cache as $record) {
			/* everything but the actual value */
			unset($record['value']);

			$records[] = $record;
		}

		return $records;
	}

	protected function cleanName(string &$name): void
	{
		/* remove wacky characters and replace with dashes */
		$name = preg_replace('@[^A-Za-z0-9/\ ]@', '-', $name);

		/* replace 2 or more namespace / with 1 */
		$name = preg_replace('@/{2,}@m', '/', $name);

		/* trim any / on the back or front of the string */
		$name = trim($name, '/');
	}

	protected function isExpired(int $expire): bool
	{
		return (time() > $expire);
	}

	protected function createExpired(int $seconds): int
	{
		return time() + $seconds;
	}
} /* end class */