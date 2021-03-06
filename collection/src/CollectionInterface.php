<?php

namespace projectorangebox\collection;

use Closure;

interface CollectionInterface
{
	const PRIORITY_LAST = 9;
	const PRIORITY_LOWEST = 5;
	const PRIORITY_LOW = 4;
	const PRIORITY_NORMAL = 3;
	const PRIORITY_HIGH = 2;
	const PRIORITY_HIGHEST = 1;
	const PRIORITY_FIRST = -1;

	public function __construct(array $config = []);
	public function has($keys = null): bool;
	public function get($keys = null, bool $flattenSingle = true): array;
	public function add(string $key, $value, int $priority = SELF::PRIORITY_NORMAL): CollectionInterface;
	public function keys(): array;
	public function remove($keys = null): CollectionInterface;
	public function changeOrganizer(string $name, Closure $closure): CollectionInterface;
	public function preventDuplicates(bool $bool): CollectionInterface;
}
