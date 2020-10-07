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
	public function __construct(array $config)
	{
		/* merge the passed into array over the default configuration */
		$this->config = array_replace(require __DIR__ . '/Config.php', $config);

		$this->makeLowercase = ($this->config['make lowercase']) ?? $this->makeLowercase;

		$this->preventDuplicates = ($this->config['prevent duplicates']) ?? $this->preventDuplicates;
	}

	/*
	add{GroupName}('value')
	add{GroupName}('value',priority)
	*/
	public function __call($name, $arguments)
	{
		if (substr($name, 0, 3) == 'add' && strlen($name) > 3) {
			$priority = ($arguments[1]) ?? self::PRIORITY_NORMAL;

			$this->add(substr($name, 3), $arguments[0], (int)$priority);
		}

		return $this;
	}

	public function add(string $group, $value, int $priority = SELF::PRIORITY_NORMAL): CollectionInterface
	{
		$this->normalize($group);

		$key = \md5(\json_encode($value));

		if (!isset($this->duplicates[$group], $this->duplicates[$group][$key])) {
			$this->sorted[$group] = false; /* not sorted anymore */

			$priority = ($priority == SELF::PRIORITY_FIRST) ? - ($this->currentIndex++) : $priority . str_pad($this->currentIndex++, 6, '0', \STR_PAD_LEFT);

			$this->collection[$group][(int)$priority] = $value;

			if ($this->preventDuplicates) {
				$this->duplicates[$group][$key] = true; /* prevent duplicates */
			}
		}

		return $this;
	}

	public function changeOrganizer(string $name, Closure $closure): CollectionInterface
	{
		$this->organizers[strtolower($name)] = $closure;

		return $this;
	}

	/**
	 * @param mixed|null $groups
	 * @return CollectionInterface
	 */
	public function remove($groups = null): CollectionInterface
	{
		foreach ($this->groups2Array($groups) as $group) {
			$this->normalize($group);

			unset($this->collection[$group]);
			unset($this->collectionSorted[$group]);
			unset($this->duplicates[$group]);
			unset($this->sorted[$group]);
		}

		return $this;
	}

	/**
	 * @param mixed|null $groups
	 * @return bool
	 */
	public function has($groups = null): bool
	{
		$has = false;

		foreach ($this->groups2Array($groups) as $group) {
			$this->normalize($group);

			if ($has = isset($this->collection[$group])) {
				break;
			}
		}

		return $has;
	}

	/**
	 * @param mixed|null $groups
	 * @return array
	 */
	public function get($groups = null, bool $flattenSingle = true): array
	{
		$matches = [];

		foreach ($this->groups2Array($groups) as $group) {
			$this->normalize($group);

			$matches[$group] = $this->getGroup($group);
		}

		/* if it's a single group do they want to return only the array values */
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

	public function groups(): array
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
	 * @param mixed|null $groups
	 * @return array
	 */
	protected function groups2Array($groups = null): array
	{
		if (is_string($groups)) {
			$groups = explode(',', $groups);
		} elseif ($groups === null) {
			$groups = array_keys($this->collection);
		}

		return (array)$groups;
	}

	/* heavy lifter - this gets a single group sorting it if nessesary */
	public function getGroup(string $group): array
	{
		$this->normalize($group);

		if (isset($this->collection[$group])) {
			/* has it already been sorted */
			if (!$this->sorted[$group]) {
				/* we store this in another array because of the organizers will work on the same data over and over */
				$this->collectionSorted[$group] = $this->collection[$group];

				ksort($this->collectionSorted[$group]);

				if (isset($this->organizers[strtolower($group)])) {
					$this->collectionSorted[$group] = $this->organizers[strtolower($group)]($this->collectionSorted[$group], $this->config);
				}

				/* mark it as sorted */
				$this->sorted[$group] = true;
			}
		}

		return ($this->collectionSorted[$group]) ? \array_values($this->collectionSorted[$group]) : [];
	}

	protected function normalize(&$string)
	{
		$string = ($this->makeLowercase) ? strtolower($string) : $string;
	}
} /* end class */