<?php

namespace projectorangebox\assets;

use Closure;

interface AssetsInterface
{
	const PRIORITY_LAST = 9;
	const PRIORITY_LOWEST = 5;
	const PRIORITY_LOW = 4;
	const PRIORITY_NORMAL = 3;
	const PRIORITY_HIGH = 2;
	const PRIORITY_HIGHEST = 1;
	const PRIORITY_FIRST = -1;

	public function __construct(array $config);
	public function has(string $variable): bool;
	public function get(string $variable): string; /* html */
	public function add(string $variable, $value): AssetsInterface;
	public function addMany(string $variable, array $records): AssetsInterface;
	public function priority(int $priority): AssetsInterface;
	public function resetPriority(): AssetsInterface; /* reset to priority normal */
	public function variables(): array; /* get a list of registered variables */
	public function changeFormatter(string $variable, Closure $closure): AssetsInterface; /* change the array to string formatter */
}
