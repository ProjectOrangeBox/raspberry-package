<?php

namespace projectorangebox\events;

use Closure;

interface EventsInterface
{
	const PRIORITY_LAST = 9;
	const PRIORITY_LOWEST = 5;
	const PRIORITY_LOW = 4;
	const PRIORITY_NORMAL = 3;
	const PRIORITY_HIGH = 2;
	const PRIORITY_HIGHEST = 1;
	const PRIORITY_FIRST = -1;

	public function register(string $name, Closure $callable, int $priority = SELF::PRIORITY_NORMAL): EventsInterface;
	public function trigger(string $name, &...$arguments): EventsInterface;
	public function has(string $name): bool;
	public function events(): array;
	public function count(string $name): int;
	public function unregister(string $name, Closure $listener): bool;
	public function unregisterAll(string $name = ''): EventsInterface;
}
