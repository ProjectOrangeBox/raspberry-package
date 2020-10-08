<?php

namespace projectorangebox\pear;

use projectorangebox\cache\CacheInterface;
use projectorangebox\views\ViewsInterface;

interface PearSkinInterface
{
	/**
	 * __construct
	 *
	 * @param string[] $config
	 * @param CacheInterface $cache
	 */
	public function __construct(array $config);
	public function getPlugin(string $name): PearInterface;
	public function hasPlugin(string $name): bool;
	public function plugins(): array;
	public function addPlugin(string $name, string $namespace): void;
} /* end class */
