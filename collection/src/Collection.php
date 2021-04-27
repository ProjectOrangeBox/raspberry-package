<?php

namespace projectorangebox\collection;

use Closure;
use projectorangebox\collection\CollectionInterface;

class Collection implements CollectionInterface
{
	protected $config;
	protected $collection = [];
	protected $collectionSorted = [];
	protected $sorted = [];
	protected $duplicates = [];
	protected $organizers = [];
	protected $makeLowercase = true;
	protected $preventDuplicates = true;
	protected $currentIndex = 0;

	/**
	 * @param array $config
	 * @return void
	 */
	public function __construct(array $config = [])
	{
		/* merge the passed into array over the default configuration */
		$this->config = array_replace(require __DIR__ . '/DefaltConfig.php', $config);

		$this->makeLowercase = ($this->config['make lowercase']) ?? $this->makeLowercase;

		$this->preventDuplicates = ($this->config['prevent duplicates']) ?? $this->preventDuplicates;
	}

	/*
	add{Key}('value')
	add{Key}('value',priority)

	$c->addPerson('Jane');
	*/
	public function __call($name, $arguments)
	{
		if (substr($name, 0, 3) == 'add' && strlen($name) > 3) {
			$priority = ($arguments[1]) ?? self::PRIORITY_NORMAL;

			$this->add(substr($name, 3), $arguments[0], (int)$priority);
		}

		return $this;
	}

	public function add(string $key, $value, int $priority = SELF::PRIORITY_NORMAL): CollectionInterface
	{
		$this->normalize($key);

		$md5Key = \md5(\json_encode($value));

		if (!isset($this->duplicates[$key], $this->duplicates[$key][$md5Key])) {
			$this->sorted[$key] = false; /* not sorted anymore */

			$this->collection[$key][$this->getNextPriority($priority)] = $value;

			if ($this->preventDuplicates) {
				$this->duplicates[$key][$md5Key] = true; /* prevent duplicates */
			}
		}

		return $this;
	}

	public function changeOrganizer(string $key, Closure $closure): CollectionInterface
	{
		$this->normalize($key);

		$this->organizers[$key] = $closure;

		return $this;
	}

	/**
	 * @param mixed|null $keys
	 * @return CollectionInterface
	 */
	public function remove($keys = null): CollectionInterface
	{
		foreach ($this->keys2Array($keys) as $key) {
			$this->normalize($key);

			unset($this->collection[$key]);
			unset($this->collectionSorted[$key]);
			unset($this->duplicates[$key]);
			unset($this->sorted[$key]);
		}

		return $this;
	}

	/**
	 * @param mixed|null $keys
	 * @return bool
	 */
	public function has($keys = null): bool
	{
		$has = false;

		foreach ($this->keys2Array($keys) as $key) {
			$this->normalize($key);

			if ($has = isset($this->collection[$key])) {
				break;
			}
		}

		return $has;
	}

	/**
	 * @param mixed|null $keys
	 * @return array
	 */
	public function get($keys = null, bool $flattenSingle = true): array
	{
		$matches = [];

		foreach ($this->keys2Array($keys) as $key) {
			$this->normalize($key);

			$matches[$key] = $this->getKey($key);
		}

		/* if it's a single key do they want to return only the array values */
		if ($flattenSingle && count($matches) == 1) {
			$matches = \array_shift($matches);
		}

		return $matches;
	}

	public function preventDuplicates(bool $bool): CollectionInterface
	{
		$this->preventDuplicates = $bool;

		return $this;
	}

	public function keys(): array
	{
		return \array_keys($this->collection);
	}

	public function debug(): array
	{
		return [
			'sorted' => $this->sorted,
			'collections' => $this->collection,
			'collectionsSorted' => $this->collectionSorted,
			'duplicates' => $this->duplicates
		];
	}

	/* protected */

	/**
	 * @param mixed|null $keys
	 * @return array
	 */
	protected function keys2Array($keys = null): array
	{
		if (is_string($keys)) {
			$keys = explode(',', $keys);
		} elseif ($keys === null) {
			$keys = array_keys($this->collection);
		}

		return (array)$keys;
	}

	/* heavy lifter - this gets a single key sorting it if nessesary */
	protected function getKey(string $key): array
	{
		$this->normalize($key);

		if (isset($this->collection[$key])) {
			/* has it already been sorted */
			if (!$this->sorted[$key]) {
				/* we store this in another array because of the organizers will work on the same data over and over */
				$this->collectionSorted[$key] = $this->collection[$key];

				ksort($this->collectionSorted[$key]);

				if (isset($this->organizers[$key])) {
					$this->collectionSorted[$key] = $this->organizers[$key]($this->collectionSorted[$key], $this->config);
				}

				/* mark it as sorted */
				$this->sorted[$key] = true;
			}
		}

		return ($this->collectionSorted[$key]) ? \array_values($this->collectionSorted[$key]) : [];
	}

	protected function normalize(&$string)
	{
		$string = ($this->makeLowercase) ? strtolower($string) : $string;
	}

	protected function getNextPriority(int $priority): int
	{
		return ($priority == SELF::PRIORITY_FIRST) ? - ($this->currentIndex++) : $priority . str_pad($this->currentIndex++, 6, '0', \STR_PAD_LEFT);
	}
} /* end class */